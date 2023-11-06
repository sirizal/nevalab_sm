<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorageTypeResource\Pages;
use App\Filament\Resources\StorageTypeResource\RelationManagers;
use App\Models\StorageType;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class StorageTypeResource extends Resource
{
    protected static ?string $model = StorageType::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Tipe Penyimpanan';

    protected static ?string $navigationGroup = 'Items';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Tipe Penyimpanan')
                            ->required()
                            ->unique(StorageType::class, 'name', ignoreRecord: true),
                        Textarea::make('description'),
                        TextInput::make('max_storage_day')
                            ->label('Max Waktu Simpan')
                            ->suffix('Hari')
                            ->numeric(),
                        TextInput::make('min_expired_date')
                            ->label('Min Expire date')
                            ->suffix('Hari')
                            ->numeric(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->wrap(),
                TextColumn::make('max_storage_day')
                    ->label('Max waktu simpan'),
                TextColumn::make('min_expired_date')
                    ->label('Min Expire date'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStorageTypes::route('/'),
        ];
    }
}
