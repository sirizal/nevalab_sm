<?php

namespace App\Filament\Resources\ApprovalFlowResource\Pages;

use App\Filament\Resources\ApprovalFlowResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageApprovalFlows extends ManageRecords
{
    protected static string $resource = ApprovalFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
