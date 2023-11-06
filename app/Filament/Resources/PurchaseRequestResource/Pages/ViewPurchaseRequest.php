<?php

namespace App\Filament\Resources\PurchaseRequestResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\Vendor;
use Illuminate\Support\Str;
use App\Models\ApprovalFlow;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use App\Models\PurchaseRequest;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use App\Exports\PurchaseRequestExport;
use App\Models\PurchaseRequestApproval;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use App\Exports\PurchaseRequestItemsExport;
use Illuminate\Support\Facades\Notification;
use App\Filament\Resources\PurchaseRequestResource;
use App\Notifications\NewPurchaseRequestNotification;
use Illuminate\Notifications\Notification as NotificationsNotification;
use Filament\Notifications\Notification as FilamentNotificationsNotification;

class ViewPurchaseRequest extends ViewRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => in_array($this->record->status, [0, 6]) && auth()->user()->hasRole(['admin', 'PRA admin'])),
            Action::make('export')
                ->label('Download PO template')
                ->action(fn ($record) => Excel::download(
                    new PurchaseRequestItemsExport($record),
                    'request_' . Str::slug($record->code) . '.xlsx',
                    \Maatwebsite\Excel\Excel::XLSX
                ))
                ->visible(fn () => $this->record->status === 6 && auth()->user()->hasRole(['admin', 'buyer'])),
            Action::make('submit_approval')
                ->label('Submit Approval')
                ->action(function ($record) {
                    $approvalFlow = ApprovalFlow::where('process_type', 'PRA')
                        ->where('client_id', $this->record->client_id)
                        ->orderBy('order', 'asc')
                        ->get();

                    //dd($approvalFlow);
                    foreach ($approvalFlow as $flow) {
                        if ($this->record->total_request_amount >= $flow->limit_amount) {
                            PurchaseRequestApproval::create([
                                'purchase_request_id' => $this->record->id,
                                'approval_type' => $flow->approval_name,
                                'user_id' => $flow->user_id
                            ]);
                        }
                    }

                    $prApproval = PurchaseRequestApproval::where('purchase_request_id', $this->record->id)
                        ->first();
                    $this->record->manager_user = $prApproval->user_id;
                    $this->record->status = 1;
                    $this->record->save();
                    Notification::route('mail', $prApproval->user->email)
                        ->notify(new NewPurchaseRequestNotification($record));
                })
                ->visible(fn () => $this->record->create_user === auth()->id() && $this->record->status === 0),
            Action::make('manager_approval')
                ->label('Manager Approval')
                ->form([
                    Select::make('status')
                        ->label('Approval Status')
                        ->options([
                            '0' => 'Approved by Manager',
                            '1' => 'Rejected by Manager'
                        ])
                        ->required(),
                    TextInput::make('manager_comment')
                        ->required()
                ])
                ->action(function (array $data, $record) {
                    //$costController = User::where('email', $data['cost_controller_user'])->first();
                    $prApproval = PurchaseRequestApproval::where('purchase_request_id', $this->record->id)
                        ->whereNull('reaction_time')
                        ->first();
                    if ($data['status'] === '0') {
                        $prApproval->reaction_time = Carbon::now();
                        $prApproval->comment = $data['manager_comment'];
                        $prApproval->reaction_desc = 'Approved by Manager';
                        $prApproval->save();
                        $prApproval1 = PurchaseRequestApproval::where('purchase_request_id', $this->record->id)->whereNull('reaction_time')->first();
                        if ($prApproval1 != null) {
                            $this->record->status = 2;
                            $this->record->manager_comment = $data['manager_comment'];
                            $this->record->manager_reaction_time = Carbon::now();
                            $this->record->cost_controller_user = $prApproval1->user_id;
                            $this->record->save();

                            Notification::route('mail', $prApproval1->user->email)
                                ->notify(new NewPurchaseRequestNotification($record));
                        } else {
                            $this->record->status = 6;
                            $this->record->manager_comment = $data['manager_comment'];
                            $this->record->manager_reaction_time = Carbon::now();
                            $this->record->save();
                            //$users = User::role('buyer')->get();
                            //Notification::send($users, new NewPurchaseRequestNotification($record));
                            Notification::route('mail', $this->record->purchaseUser->email)
                                ->notify(new NewPurchaseRequestNotification($record));
                        }
                    } else {
                        $this->record->status = 3;
                        $this->record->manager_comment = $data['manager_comment'];
                        $this->record->save();
                        $prApproval->reaction_time = Carbon::now();
                        $prApproval->comment = $data['manager_comment'];
                        $prApproval->reaction_desc = 'Rejected by Manager';
                        $prApproval->save();
                        Notification::route('mail', $this->record->createUser->email)
                            ->notify(new NewPurchaseRequestNotification($record));
                    }
                })
                ->visible(fn () => $this->record->manager_user === auth()->id() && $this->record->status === 1),
            Action::make('cc_approval')
                ->label('Costing Manager Approval')
                ->form([
                    Select::make('status')
                        ->label('Approval Status')
                        ->options([
                            '0' => 'Approved by Costing Manager',
                            '1' => 'Rejected by Costing Manager'
                        ])
                        ->required(),
                    TextInput::make('cost_controller_comment')
                        ->required()
                ])
                ->action(function (array $data, $record) {
                    $prApproval = PurchaseRequestApproval::where('purchase_request_id', $this->record->id)
                        ->whereNull('reaction_time')
                        ->first();
                    if ($data['status'] === '0') {
                        $prApproval->reaction_time = Carbon::now();
                        $prApproval->comment = $data['cost_controller_comment'];
                        $prApproval->reaction_desc = 'Approved by Costing Manager';
                        $prApproval->save();
                        $prApproval1 = PurchaseRequestApproval::where('purchase_request_id', $this->record->id)->whereNull('reaction_time')->first();
                        if ($prApproval1 != null) {
                            $this->record->status = 4;
                            $this->record->cost_controller_comment = $data['cost_controller_comment'];
                            $this->record->cost_controller_reaction_time = Carbon::now();
                            $this->record->kam_user = $prApproval1->user_id;
                            $this->record->save();
                            Notification::route('mail', $prApproval1->user->email)
                                ->notify(new NewPurchaseRequestNotification($record));
                        } else {
                            $this->record->status = 6;
                            $this->record->cost_controller_comment = $data['cost_controller_comment'];
                            $this->record->cost_controller_reaction_time = Carbon::now();
                            $this->record->save();
                            //$users = User::role('buyer')->get();
                            //Notification::send($users, new NewPurchaseRequestNotification($record));
                            Notification::route('mail', $this->record->purchaseUser->email)
                                ->notify(new NewPurchaseRequestNotification($record));
                        }
                    } else {
                        $this->record->status = 5;
                        $this->record->cost_controller_comment = $data['cost_controller_comment'];
                        $this->record->save();
                        $prApproval->reaction_time = Carbon::now();
                        $prApproval->comment = $data['cost_manager_comment'];
                        $prApproval->reaction_desc = 'Rejected by Costing Manager';
                        $prApproval->save();
                        Notification::route('mail', $this->record->createUser->email)
                            ->notify(new NewPurchaseRequestNotification($record));
                    }
                })
                ->visible(fn () => $this->record->cost_controller_user === auth()->id() && $this->record->status === 2),
            Action::make('kam_approval')
                ->label('KAM Approval')
                ->form([
                    Select::make('status')
                        ->label('Approval Status')
                        ->options([
                            '0' => 'Approved by KAM',
                            '1' => 'Rejected by KAM'
                        ])
                        ->required(),
                    TextInput::make('kam_comment')
                        ->required()
                ])
                ->action(function (array $data, $record) {
                    $prApproval = PurchaseRequestApproval::where('purchase_request_id', $this->record->id)
                        ->whereNull('reaction_time')
                        ->first();
                    if ($data['status'] === '0') {
                        $prApproval->reaction_time = Carbon::now();
                        $prApproval->comment = $data['kam_comment'];
                        $prApproval->reaction_desc = 'Approved by KAM';
                        $prApproval->save();
                        $this->record->status = 6;
                        $this->record->kam_comment = $data['kam_comment'];
                        $this->record->kam_reaction_time = Carbon::now();
                        $this->record->save();

                        //$users = User::role('buyer')->get();
                        //Notification::send($users, new NewPurchaseRequestNotification($record));
                        Notification::route('mail', $this->record->purchaseUser->email)
                            ->notify(new NewPurchaseRequestNotification($record));
                    } else {
                        $this->record->status = 7;
                        $this->record->cost_controller_comment = $data['kam_comment'];
                        $this->record->save();

                        $prApproval->reaction_time = Carbon::now();
                        $prApproval->comment = $data['kam_comment'];
                        $prApproval->reaction_desc = 'Rejected by KAM';
                        $prApproval->save();

                        Notification::route('mail', $this->record->createUser->email)
                            ->notify(new NewPurchaseRequestNotification($record));
                    }
                })
                ->visible(fn () => $this->record->kam_user === auth()->id() && $this->record->status === 4),
            Action::make('PO Info')
                ->label('PO Info')
                ->form([
                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->required()
                        ->options(Vendor::query()->pluck('name', 'id')->toArray())
                        ->searchable(),
                    TextInput::make('purchase_no')
                        ->label('PO No')
                        ->required()
                        ->unique(PurchaseRequest::class, 'purchase_no', ignoreRecord: true),
                    DatePicker::make('purchase_date')
                        ->required(),
                    FileUpload::make('purchase_no_file')
                        ->helperText('Silahkan upload file PO dalam format pdf')
                        ->preserveFilenames()
                        ->downloadable()
                        ->acceptedFileTypes(['application/pdf'])
                ])
                ->action(function (array $data, $record) {
                    $this->record->vendor_id = $data['vendor_id'];
                    $this->record->purchase_no = $data['purchase_no'];
                    $this->record->purchase_date = $data['purchase_date'];
                    $this->record->purchase_no_file = $data['purchase_no_file'];
                    $this->record->purchase_user = auth()->id();
                    $this->record->status = 8;

                    $this->record->save();
                })
                ->visible(fn () => $this->record->status === 6 && auth()->user()->hasRole(['admin', 'buyer'])),
            Action::make('export_2')
                ->label('Update PO template')
                ->action(fn ($record) => Excel::download(
                    new PurchaseRequestExport($record),
                    'update_request_' . Str::slug($record->code) . '.xlsx',
                    \Maatwebsite\Excel\Excel::XLSX
                ))
                ->visible(fn () => $this->record->status === 6 && auth()->user()->hasRole(['admin', 'buyer'])),
            Actions\EditAction::make('delivery')
                ->label('Input delivery')
                ->visible(fn () => $this->record->status === 8 && auth()->user()->hasRole(['admin', 'supplier']) && $this->record->vendor_id === auth()->user()->vendor_id),
        ];
    }
}
