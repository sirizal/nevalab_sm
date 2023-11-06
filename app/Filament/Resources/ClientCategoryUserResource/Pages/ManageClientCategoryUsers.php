<?php

namespace App\Filament\Resources\ClientCategoryUserResource\Pages;

use App\Filament\Resources\ClientCategoryUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClientCategoryUsers extends ManageRecords
{
    protected static string $resource = ClientCategoryUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
