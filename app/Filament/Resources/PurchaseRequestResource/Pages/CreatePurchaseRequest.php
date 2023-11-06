<?php

namespace App\Filament\Resources\PurchaseRequestResource\Pages;

use Filament\Actions;
use Illuminate\Support\Carbon;
use Doctrine\DBAL\Schema\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification;
use App\Filament\Resources\PurchaseRequestResource;
use App\Notifications\NewPurchaseRequestNotification;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreatePurchaseRequest extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PurchaseRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['create_user'] = auth()->id();
        $data['request_date'] = Carbon::now();
        $data['code'] = make_purchase_request_no();

        if ($data['need_approval_flag'] === 0) {
            $data['status'] = 6;
        } else {
            $data['status'] = 0;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $pr = $this->record;

        if ($pr->status === 6) {
            Notification::route('mail', $this->record->purchaseUser->email)
                ->notify(new NewPurchaseRequestNotification($pr));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Order Details')
                ->schema([
                    Section::make()->schema(PurchaseRequestResource::getFormSchema())->columns(3),
                ]),

            Step::make('Order Items')
                ->schema([
                    Section::make()->schema(PurchaseRequestResource::getFormSchema('items')),
                ])
        ];
    }
}
