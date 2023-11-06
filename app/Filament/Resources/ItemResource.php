<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemType;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Item';

    protected static ?string $navigationGroup = 'Items';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Produk')
                            ->required()
                            ->unique(Item::class, 'code', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Barang')
                            ->required(),
                        TextInput::make('short_description')
                            ->label('Deskripsi(pendek)')
                    ])->columns(3),
                Section::make()
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi Barang')
                            ->helperText('Silahkan isikan spesifikasi barang')
                            ->required()
                    ])->columns(1),
                Section::make()
                    ->schema([
                        Select::make('item_type_id')
                            ->label('Tipe Produk')
                            ->relationship('item_type', 'name'),
                        Select::make('storage_type_id')
                            ->label('Tipe Penyimpanan')
                            ->relationship('storage_type', 'name'),
                        Select::make('uom_id')
                            ->label('Satuan Produk')
                            ->relationship('uom', 'code'),
                        Select::make('category_1')
                            ->label('Kategori 1')
                            ->searchable()
                            ->options(Category::whereNull('parent_id')->pluck('name', 'id')->toArray())
                            ->live()
                            ->afterStateUpdated(
                                function (callable $set) {
                                    $set('category_2', null);
                                    $set('category_3', null);
                                    $set('category_4', null);
                                }
                            ),
                        Select::make('category_2')
                            ->label('Kategori 2')
                            ->searchable()
                            ->options(
                                function ($get) {
                                    $category1 = $get('category_1');
                                    if ($category1) {
                                        return Category::where('parent_id', $category1)
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                }
                            )
                            ->live()
                            ->afterStateUpdated(
                                fn (callable $set) => $set('category_3', null)
                            ),
                        Select::make('category_3')
                            ->label('Kategori 3')
                            ->searchable()
                            ->options(
                                function ($get) {
                                    $category2 = $get('category_2');
                                    if ($category2) {
                                        return Category::where('parent_id', $category2)
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                }
                            )
                            ->live()
                            ->afterStateUpdated(
                                fn (callable $set) => $set('category_4', null)
                            ),
                        Select::make('category_4')
                            ->label('Kategori 4')
                            ->searchable()
                            ->options(
                                function ($get) {
                                    $category3 = $get('category_3');
                                    if ($category3) {
                                        return Category::where('parent_id', $category3)
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                }
                            )
                            ->live(),
                    ])->columns(3),
                Section::make()
                    ->schema([
                        Toggle::make('active')
                            ->label('Active')
                            ->helperText('Status Produk')
                            ->default(true),
                        Toggle::make('critical_item')
                            ->label('Kritikal Produk')
                            ->helperText('Produk termasuk kritikal atau tidak')
                            ->default(false),
                        TextInput::make('standard_cost')
                            ->numeric()
                            ->default(0)
                            ->rules(['integer', 'min:0']),
                        TextInput::make('grammage')
                            ->numeric()
                            ->default(0)
                            ->rules(['integer', 'min:0']),
                        TextInput::make('brand'),
                        TextInput::make('barcode')
                            ->label('Barcode (ISBN, UPC, GTIN, etc.)')
                            ->unique(Item::class, 'barcode', ignoreRecord: true)
                    ])->columns(3),
                Section::make()
                    ->schema([
                        FileUpload::make('sps_file')
                            ->label('Sps')
                            ->helperText('Silahkan upload file Standard Purchase Specification (sps) dalam format pdf')
                            ->preserveFilenames()
                            ->downloadable()
                            ->acceptedFileTypes(['application/pdf'])
                    ])->columns(1),
                Section::make('images')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('media')
                            ->collection('product-images')
                            ->multiple()
                            ->maxFiles(5)
                            ->hiddenLabel()
                    ])->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('product-image')
                    ->label('image')
                    ->collection('product-images'),
                TextColumn::make('code')
                    ->label('Kode Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('uom.name')
                    ->label('Satuan Barang'),
                TextColumn::make('storage_type.name')
                    ->label('Tipe Penyimpanan'),
                IconColumn::make('active')
                    ->label('Active Status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('category1.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
