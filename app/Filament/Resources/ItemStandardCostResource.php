<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Item;
use App\Models\Site;
use Filament\Tables;
use App\Models\Client;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ItemStandardCost;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ItemStandardCostResource\Pages;
use App\Filament\Resources\ItemStandardCostResource\RelationManagers;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class ItemStandardCostResource extends Resource
{
    protected static ?string $model = ItemStandardCost::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Item Standard Cost';

    protected static ?string $navigationGroup = 'Items';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('client_id')
                            ->label('Klien')
                            ->options(Client::query()->pluck('code', 'id')->toArray())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(
                                function ($set) {
                                    $set('site_id', null);
                                }
                            ),
                        Select::make('site_id')
                            ->label('Site')
                            ->searchable()
                            ->options(
                                function ($get) {
                                    $client = $get('client_id');
                                    if ($client) {
                                        return Site::where('client_id', $client)
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                }
                            ),
                        Select::make('item_id')
                            ->label('Item')
                            ->options(Item::query()->pluck('name', 'id')->toArray())
                            ->searchable(),
                        TextInput::make('standard_cost')
                            ->label('Standard Cost')
                            ->numeric()
                            ->required(),
                        DatePicker::make('start_date')
                            ->label('Berlaku Mulai')
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label('Berlaku Sampai')
                            ->native(false),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.code'),
                TextColumn::make('site.name'),
                TextColumn::make('item.name'),
                TextColumn::make('standard_cost'),
                TextColumn::make('start_date')
                    ->date(),
                TextColumn::make('end_date')
                    ->date()
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageItemStandardCosts::route('/'),
        ];
    }
}
