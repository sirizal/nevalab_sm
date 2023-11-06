<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\Uom;
use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use App\Models\Vendor;
use Filament\Forms\Form;
use App\Models\VendorItem;
use Filament\Tables\Table;
use Squire\Models\Currency;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VendorItemResource\Pages;
use App\Filament\Resources\VendorItemResource\RelationManagers;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class VendorItemResource extends Resource
{
    protected static ?string $model = VendorItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-ellipsis-horizontal-circle';

    protected static ?string $navigationLabel = 'Vendor Item';

    protected static ?string $navigationGroup = 'Vendors';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(Vendor::all()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required(),
                        Select::make('item_id')
                            ->label('Item/Produk')
                            ->options(Item::where('active', 1)->get()->pluck('name', 'id')->toArray())
                            ->searchable(),
                        TextInput::make('price')
                            ->label('Harga Beli')
                            ->numeric()
                            ->rules(['integer', 'min:1'])
                            ->required(),
                        DatePicker::make('start_date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if (!$state) {
                                    $set('status', 1);
                                } else {
                                    $set('status', 0);
                                }
                            }),
                        Hidden::make('status')->default(1)
                    ])
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vendor.code')
                    ->label('Kode Vendor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga Beli')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => number_format($state, 0, '.', ',')),
                TextColumn::make('uom.code')
                    ->label('Satuan'),
                TextColumn::make('start_date')
                    ->label('Tgl Berlaku')
                    ->date(),
                TextColumn::make('end_date')
                    ->label('Tgl Berakhir')
                    ->date(),
                IconColumn::make('status')
                    ->label('Aktif')
                    ->boolean()
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
            'index' => Pages\ManageVendorItems::route('/'),
        ];
    }
}
