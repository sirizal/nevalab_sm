<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Filament\Resources\WarehouseResource\RelationManagers;
use App\Models\Client;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Gudang';

    protected static ?string $navigationGroup = 'Clients';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view warehouses');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('client_id')
                            ->label('Klien')
                            ->options(Client::all()->pluck('code', 'id')->toArray())
                            ->searchable(),
                        TextInput::make('code')
                            ->label('Kode Gudang')
                            ->required()
                            ->unique(Warehouse::class, 'code', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Gudang')
                            ->required(),
                        TextInput::make('address1'),
                        TextInput::make('address2'),
                        TextInput::make('address3'),
                        TextInput::make('city'),
                        TextInput::make('postal_code'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.code')
                    ->label('Klien')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('address1')
                    ->wrap(),
                TextColumn::make('address2')
                    ->wrap(),
                TextColumn::make('address3')
                    ->wrap(),
                TextColumn::make('city')
                    ->wrap(),
                TextColumn::make('postal_code')
                    ->wrap()
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
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWarehouses::route('/'),
        ];
    }
}
