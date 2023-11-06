<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\Uom;
use Filament\Forms;
use App\Models\Item;
use App\Models\Site;
use App\Models\User;
use Filament\Tables;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Category;
use Filament\Forms\Form;
use App\Models\Warehouse;
use App\Models\VendorItem;
use Filament\Tables\Table;
use App\Models\ServiceType;
use Illuminate\Support\Carbon;
use App\Models\PurchaseRequest;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\PurchaseRequestItem;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use function PHPUnit\Framework\isNull;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\CreateRecord;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PurchaseRequestResource\Pages;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\ClientCategoryUser;
use App\Models\ItemStandardCost;
use Filament\Tables\Actions\Action;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Purchase Request';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Order Details')
                            ->schema(static::getFormSchema())
                            ->columns(3),
                        Section::make('Order Items')
                            ->schema(static::getFormSchema('items'))
                    ])
                    ->columnSpan(['lg' => 3, 'md' => 3])
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('PRA No')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('issconnect_mr_no')
                    ->label('MR No')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('purchase_no')
                    ->label('PO No')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('vendor.name')
                    ->label('Supplier'),
                TextColumn::make('purchaseUser.name')
                    ->label('Buyer'),
                TextColumn::make('request_date')
                    ->label('Tgl Request')
                    ->date(),
                TextColumn::make('request_delivery_date')
                    ->label('Req Delivery')
                    ->date(),
                TextColumn::make('request_receive_date')
                    ->label('Req Receive')
                    ->date(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'grey',
                        '1' => 'warning',
                        '2' => 'warning',
                        '3' => 'danger',
                        '4' => 'warning',
                        '5' => 'danger',
                        '6' => 'warning',
                        '7' => 'success',
                        '8' => 'success',
                        '9' => 'success',
                        '10' => 'success',
                        '11' => 'success',
                        '12' => 'success',
                        '13' => 'success',
                        '14' => 'success'
                    })
                    ->formatStateUsing(
                        function ($state) {
                            switch ($state) {
                                case '0':
                                    return 'New';
                                case '1':
                                    return 'Waiting manager approve';
                                case '2':
                                    return 'Waiting cost controller approve';
                                case '3':
                                    return 'Rejected by manager';
                                case '4':
                                    return 'Waiting KAM approve';
                                case '5':
                                    return 'Rejected by cost controller';
                                case '6':
                                    return 'Ready for PO';
                                case '7':
                                    return 'Rejected by KAM';
                                case '8':
                                    return 'Waiting delivery';
                                case '9':
                                    return 'Delivered';
                                case '10':
                                    return 'Finish inspection';
                                case '11':
                                    return 'Received';
                                case '12':
                                    return 'Invoiced';
                                case '13':
                                    return 'Invoice posted';
                                case '14':
                                    return 'Closed';
                            }
                        }
                    ),
                TextColumn::make('total_request_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn (string $state): string => number_format($state, 0, '.', ','))

            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('Pdf')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (PurchaseRequest $record): string => route('pr.pdf.download', ['record' => $record]))
                    ->openUrlInNewTab(),
                //Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPurchaseRequests::route('/'),
            'create' => Pages\CreatePurchaseRequest::route('/create'),
            'view' => Pages\ViewPurchaseRequest::route('/{record}'),
            'edit' => Pages\EditPurchaseRequest::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema(string $section = null): array
    {
        if ($section === 'items') {
            return [
                Repeater::make('purchaseRequestItems')
                    ->relationship('purchaseRequestItems')
                    ->schema([
                        Select::make('item_id')
                            ->label('Product')
                            ->searchable()
                            ->live()
                            ->options(
                                function ($get) {
                                    return ItemStandardCost::with('item')
                                        ->whereRelation('item', 'category_1', $get('../../category_id'))
                                        ->where('site_id', $get('../../site_id'))
                                        ->whereNull('end_date')
                                        ->get()
                                        ->pluck('item.name', 'item_id')
                                        ->toArray();
                                }
                            )
                            ->afterStateUpdated(
                                function ($state, $set, $get) {
                                    $itemStandardCost = ItemStandardCost::where('item_id', $state)
                                        ->whereNull('end_date')
                                        ->first();
                                    $set('standard_cost', (string)($itemStandardCost->standard_cost));
                                    $set('description', (string)($itemStandardCost->item->name));
                                    $set('uom_id', (string)($itemStandardCost->item->uom_id));
                                }
                            )
                            ->columnSpan([
                                'md' => 5,
                            ])
                            ->hidden(fn ($get) => $get('../../purchase_type') === 'gl')
                            ->disabled(fn ($get) => $get('../../status') === 8),
                        TextInput::make('gl_no')
                            ->columnSpan([
                                'md' => 2,
                            ])
                            ->visible(fn ($get) => $get('../../purchase_type') === 'gl')
                            ->disabled(fn ($get) => $get('../../status') === 8),
                        TextInput::make('description')
                            ->columnSpan([
                                'md' => 5,
                            ])
                            ->visible(fn ($get) => $get('../../purchase_type') === 'gl'),
                        TextInput::make('request_qty')
                            ->numeric()
                            ->rules(['integer', 'min:1'], fn ($livewire) => $livewire instanceof CreateRecord)
                            ->required()
                            ->default(1)
                            ->disabled(fn ($get) => $get('../../status') === 8)
                            ->columnSpan([
                                'md' => 2,
                            ]),
                        Select::make('uom_id')
                            ->label('Satuan')
                            ->options(Uom::query()->pluck('code', 'id')->toArray())
                            ->disabled(fn ($get) => $get('../../status') === 8)
                            ->columnSpan([
                                'md' => 2,
                            ]),
                        TextInput::make('standard_cost')
                            ->numeric()
                            ->rules(['integer', 'min:1'])
                            ->required()
                            ->disabled(fn ($get) => $get('../../status') === 8)
                            ->columnSpan([
                                'md' => 2,
                            ]),
                        TextInput::make('variant_code')
                            ->columnSpan([
                                'md' => 2,
                            ]),
                        Hidden::make('description'),
                        TextInput::make('storage_type')
                            ->label('Tipe Penyimpanan')
                            ->disabled()
                            ->afterStateHydrated(function ($get, TextInput $component) {
                                $item = Item::where('id', $get('item_id'))->first();
                                $component->state($item->storage_type->name ?? '');
                            })
                            ->dehydrated(false)
                            ->visible(fn ($livewire) => $livewire instanceof EditRecord)
                            ->columnSpan([
                                'md' => 2,
                            ]),
                        TextInput::make('toreceive_qty')
                            ->label('Delivery Qty')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn ($get, $livewire) => $get('status') === 0 || $livewire instanceof EditRecord)
                            ->columnSpan([
                                'md' => 2,
                            ]),
                        Repeater::make('tallySheets')
                            ->relationship('tallySheets')
                            ->schema([
                                TextInput::make('pack_qty')
                                    ->label('Koli')
                                    ->numeric()
                                    ->rules(['integer', 'min:1'])
                                    ->required()
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $set('total_qty', $state * $get('qty_per_pack'));
                                    })
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                                Select::make('pack_uom')
                                    ->label('Satuan Koli')
                                    ->required()
                                    ->options(Uom::query()->pluck('code', 'id')->toArray())
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                                TextInput::make('qty_per_pack')
                                    ->numeric()
                                    ->rules(['integer', 'min:1'])
                                    ->required()
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $set('total_qty', $state * $get('pack_qty'));
                                        $set('qty_uom', $get('../../uom_id'));
                                    })
                                    ->default(1)
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                                TextInput::make('total_qty')
                                    ->numeric()
                                    ->disabled()
                                    ->required()
                                    ->live()
                                    ->dehydrated()
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                                DatePicker::make('production_date')
                                    ->label('Tanggal Produksi')
                                    ->native(false)
                                    ->minDate(function ($get) {
                                        $deliveryDate = $get('../../../../delivery_date');
                                        $storage_type = $get('../../storage_type');
                                        if ($deliveryDate != null) {
                                            if ($storage_type == 'Frozen Storage') {
                                                return Carbon::parse($deliveryDate)->subDays(14);
                                            } elseif ($storage_type == 'Dry Storage') {
                                                return Carbon::parse($deliveryDate)->subDays(30);
                                            } else {
                                                return Carbon::parse($deliveryDate)->subDays(3);
                                            }
                                        }
                                        return null;
                                    })
                                    ->maxDate(function ($get) {
                                        $deliveryDate = $get('../../../../../delivery_date');
                                        if ($deliveryDate != null) {
                                            return Carbon::parse($deliveryDate);
                                        }
                                        return null;
                                    })
                                    ->closeOnDateSelection()
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                                DatePicker::make('expire_date')
                                    ->native(false)
                                    ->label('Tanggal Expire')
                                    ->minDate(function ($get) {
                                        $deliveryDate = $get('../../../../delivery_date');
                                        $storage_type = $get('../../storage_type');
                                        if ($deliveryDate != null) {
                                            if ($storage_type == 'Frozen Storage') {
                                                return Carbon::parse($deliveryDate)->addDays(60);
                                            } elseif ($storage_type == 'Dry Storage') {
                                                return Carbon::parse($deliveryDate)->addDays(365);
                                            } else {
                                                return Carbon::parse($deliveryDate)->addDays(3);
                                            }
                                        }
                                        return null;
                                    })
                                    ->closeOnDateSelection()
                                    ->columnSpan([
                                        'md' => 2,
                                    ]),
                                Hidden::make('qty_uom'),
                                Placeholder::make('calculatedQty')
                                    ->content(function ($get, $set) {
                                        $itemsColumn = array_column($get('../../tallySheets'), 'total_qty');
                                        $sumItemsColumn = array_sum($itemsColumn);
                                        $set('../../toreceive_qty', $sumItemsColumn);
                                    })
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, PurchaseRequestItem $record): array {
                                $data['purchase_request_id'] = $record->purchase_request_id;

                                return $data;
                            })
                            ->live()
                            ->visible(fn ($get, $livewire) => $get('status') === 0 || $livewire instanceof EditRecord)
                            ->defaultItems(1)
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(1)
                    ->columns([
                        'md' => 10,
                    ])
                    ->hiddenLabel()
                    ->required()
                    ->deletable(fn ($get, $livewire) => $get('status') === 0 || $livewire instanceof CreateRecord)
                    ->addable(fn ($get, $livewire) => $get('status') === 0 || $livewire instanceof CreateRecord)
            ];
        }

        return [
            Select::make('client_id')
                ->label('Klien')
                ->options(Client::all()->pluck('code', 'id')->toArray())
                ->searchable()
                ->required()
                ->live()
                ->disabled(fn ($get) => $get('status') === 8)
                ->afterStateUpdated(
                    function (callable $set) {
                        $set('service_type_id', null);
                        $set('warehouse_id', null);
                        $set('site_id', null);
                    }
                ),
            Select::make('site_id')
                ->label('Site')
                ->options(
                    function ($get) {
                        $clientId = $get('client_id');
                        if ($clientId) {
                            return Site::where('client_id', $clientId)
                                ->get()
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                    }
                )
                ->searchable()
                ->disabled(fn ($get) => $get('status') === 8)
                ->required(),
            Select::make('service_type_id')
                ->label('Layanan Service')
                ->options(
                    function ($get) {
                        $clientId = $get('client_id');
                        if ($clientId) {
                            return ServiceType::where('client_id', $clientId)
                                ->get()
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                    }
                )
                ->searchable()
                ->disabled(fn ($get) => $get('status') === 8)
                ->required(),
            Select::make('warehouse_id')
                ->label('Gudang Penerima')
                ->options(
                    function ($get) {
                        $clientId = $get('client_id');
                        if ($clientId) {
                            return Warehouse::where('client_id', $clientId)
                                ->get()
                                ->pluck('code', 'id')
                                ->toArray();
                        }
                    }
                )
                ->searchable()
                ->disabled(fn ($get) => $get('status') === 8)
                ->required(),
            Select::make('category_id')
                ->label('Category')
                ->options(Category::all()->pluck('name', 'id')->toArray())
                ->disabled(fn ($get) => $get('status') === 8)
                ->searchable()
                ->afterStateUpdated(
                    function ($state, $set, $get) {
                        //$cat = Category::where('id', $state)->first();
                        $cat = ClientCategoryUser::where('category_id', $state)
                            ->where('client_id', $get('client_id'))
                            ->first();
                        $set('purchase_user', $cat->user_id);
                    }
                )
                ->required(),
            Hidden::make('purchase_user'),
            DatePicker::make('request_delivery_date')
                ->label('Request Tgl Kirim')
                ->required()
                ->native(false)
                ->live()
                ->afterStateUpdated(
                    function (callable $set) {
                        $set('request_receive_date', null);
                    }
                )
                ->closeOnDateSelection()
                ->disabled(fn ($get) => $get('status') === 8),
            DatePicker::make('request_receive_date')
                ->label('Request Tgl Terima')
                ->required()
                ->native(false)
                ->minDate(
                    function ($get) {
                        $reqDeliveryDate = $get('request_delivery_date');
                        if ($reqDeliveryDate != null) {
                            return Carbon::parse($reqDeliveryDate);
                        }
                        return null;
                    }
                )
                ->closeOnDateSelection()
                ->disabled(fn ($get) => $get('status') === 8),
            Select::make('purchase_type')
                ->options([
                    'item' => 'item',
                    'gl' => 'gl'
                ])
                ->default('item')
                ->disabled(fn ($livewire) => $livewire instanceof EditRecord),
            Select::make('request_type')
                ->label('Tipe Request')
                ->options([
                    0 => 'Goods',
                    1 => 'Service'
                ])
                ->required()
                ->disabled(fn ($get) => $get('status') === 8),
            Select::make('process_type')
                ->label('Proses Order')
                ->options([
                    0 => 'Regular',
                    1 => 'Additional',
                    2 => 'PRA after fact'
                ])
                ->required()
                ->disabled(fn ($get) => $get('status') === 8),
            Select::make('need_approval_flag')
                ->label('Tipe Approval')
                ->options([
                    0 => 'Approval By ISSConnect',
                    1 => 'Approval By Nevalab'
                ])
                ->default(0)
                ->disabled(fn ($get) => $get('status') === 8),
            Textarea::make('remarks')
                ->label('Keterangan'),
            Select::make('vendor_id')
                ->label('Supplier')
                ->options(Vendor::all()->pluck('name', 'id')->toArray())
                ->disabled(fn ($get) => $get('status') === 8)
                ->searchable(),
            TextInput::make('purchase_no')
                ->label('PO No')
                ->disabled(fn ($get) => $get('status') === 8)
                ->visible(fn ($livewire) => $livewire instanceof EditRecord),
            DatePicker::make('purchase_date')
                ->label('Tanggal PO')
                ->native(false)
                ->disabled(fn ($get) => $get('status') === 8)
                ->visible(fn ($livewire) => $livewire instanceof EditRecord),
            Select::make('purchase_user')
                ->options(User::all()->pluck('name', 'id')->toArray())
                ->disabled(fn ($get) => $get('status') === 8)
                ->searchable()
                ->visible(fn ($livewire) => $livewire instanceof EditRecord),
            FileUpload::make('purchase_no_file')
                ->helperText('Silahkan upload file purchase order dalam format pdf')
                ->preserveFilenames()
                ->downloadable()
                ->acceptedFileTypes(['application/pdf'])
                ->visible(fn ($livewire) => $livewire instanceof EditRecord)
                ->disabled(fn ($get) => $get('status') === 8),
            TextInput::make('delivery_no')
                ->label('No Surat Jalan')
                ->required(fn ($get) => $get('status') === 8)
                ->visible(fn ($get) => $get('status') === 8),
            DatePicker::make('delivery_date')
                ->label('Tanggal Delivery')
                ->native(false)
                ->live()
                ->required(fn ($get) => $get('status') === 8)
                ->visible(fn ($get) => $get('status') === 8),
            TextInput::make('vehicle_no')
                ->label('No Mobil')
                ->required(fn ($get) => $get('status') === 8)
                ->visible(fn ($get) => $get('status') === 8),
            TextInput::make('driver_name')
                ->label('Nama Sopir')
                ->required(fn ($get) => $get('status') === 8)
                ->visible(fn ($get) => $get('status') === 8),
            TextInput::make('driver_ktp')
                ->label('No KTP Sopir')
                ->required(fn ($get) => $get('status') === 8)
                ->visible(fn ($get) => $get('status') === 8),
            TextInput::make('driver_phone_no')
                ->label('No HP Sopir')
                ->required(fn ($get) => $get('status') === 8)
                ->visible(fn ($get) => $get('status') === 8),
            Select::make('delivery_by')
                ->label('Pengiriman menggunakan')
                ->options([
                    'Mobil sendiri' => 'Mobil sendiri',
                    'Expedisi' => 'Expedisi',
                    'Expedisi ISS' => 'Expedisi ISS'
                ])
                ->required(fn ($get) => $get('status') === 8)
                ->visible(fn ($get) => $get('status') === 8),
            TextInput::make('issconnect_mr_no')
                ->label('MR No'),
            SpatieMediaLibraryFileUpload::make('media')
                ->collection('vehicle-images')
                ->label('Foto kendaraan')
                ->multiple()
                ->maxFiles(5)
                ->image()
                ->visible(fn ($get) => $get('status') === 8)
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Fieldset::make('Order Details')
                    ->schema([
                        TextEntry::make('client.code')
                            ->label('Klien'),
                        TextEntry::make('site.name')
                            ->label('Site'),
                        TextEntry::make('serviceType.name')
                            ->label('Jenis Layanan'),
                        TextEntry::make('warehouse.code')
                            ->label('Gudang Penerima'),
                        TextEntry::make('request_date')
                            ->label('Tanggal Request')
                            ->date(),
                        TextEntry::make('request_delivery_date')
                            ->label('Request Tanggal Kirim')
                            ->date(),
                        TextEntry::make('request_receive_date')
                            ->label('Request Tanggal Terima')
                            ->date(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                '0' => 'grey',
                                '1' => 'warning',
                                '2' => 'warning',
                                '3' => 'danger',
                                '4' => 'warning',
                                '5' => 'danger',
                                '6' => 'warning',
                                '7' => 'success',
                                '8' => 'success',
                                '9' => 'success',
                                '10' => 'success',
                                '11' => 'success',
                                '12' => 'success',
                                '13' => 'success',
                                '14' => 'success'
                            })
                            ->formatStateUsing(
                                function ($state) {
                                    switch ($state) {
                                        case '0':
                                            return 'New';
                                        case '1':
                                            return 'Waiting manager approve';
                                        case '2':
                                            return 'Waiting cost controller approve';
                                        case '3':
                                            return 'Rejected by manager';
                                        case '4':
                                            return 'Waiting KAM approve';
                                        case '5':
                                            return 'Rejected by cost controller';
                                        case '6':
                                            return 'Ready for PO';
                                        case '7':
                                            return 'Rejected by KAM';
                                        case '8':
                                            return 'Waiting Delivery';
                                        case '9':
                                            return 'Delivered';
                                        case '10':
                                            return 'Waiting Inspection';
                                        case '11':
                                            return 'Received';
                                        case '12':
                                            return 'Invoiced';
                                        case '13':
                                            return 'Invoice posted';
                                        case '14':
                                            return 'Closed';
                                    }
                                }
                            ),
                        TextEntry::make('total_request_amount')
                            ->label('Request Amount')
                            ->formatStateUsing(fn (string $state): string => number_format($state, 0, '.', ',')),
                        TextEntry::make('request_type')
                            ->label('Tipe Produk')
                            ->formatStateUsing(
                                function ($state) {
                                    switch ($state) {
                                        case '0':
                                            return 'Goods';
                                        case '1':
                                            return 'Service';
                                    }
                                }
                            ),
                        TextEntry::make('process_type')
                            ->label('Tipe Request')
                            ->formatStateUsing(
                                function ($state) {
                                    switch ($state) {
                                        case '0':
                                            return 'Reguler';
                                        case '1':
                                            return 'Additional';
                                        case '2':
                                            return 'PRA after fact';
                                    }
                                }
                            ),
                        TextEntry::make('vat_code')
                            ->label('Tipe Pajak')
                            ->formatStateUsing(
                                function ($state) {
                                    switch ($state) {
                                        case '0':
                                            return 'Non PPN';
                                        case '1':
                                            return 'PPN 11%';
                                        case '2':
                                            return 'PPN 1%';
                                    }
                                }
                            ),
                        TextEntry::make('remarks')
                            ->label('Keterangan'),
                        TextEntry::make('category.name')
                            ->label('Category'),
                        RepeatableEntry::make('purchaseRequestItems')
                            ->columns(7)
                            ->schema([
                                TextEntry::make('item.code')
                                    ->label('Kode Item'),
                                TextEntry::make('description')
                                    ->columnSpan(2),
                                TextEntry::make('request_qty')
                                    ->numeric(
                                        decimalPlaces: 0,
                                        decimalSeparator: '.',
                                        thousandsSeparator: ',',
                                    ),
                                TextEntry::make('uom.code'),
                                TextEntry::make('standard_cost')
                                    ->numeric(
                                        decimalPlaces: 0,
                                        decimalSeparator: '.',
                                        thousandsSeparator: ',',
                                    ),
                                TextEntry::make('total')
                                    ->state(function (PurchaseRequestItem $record): float {
                                        return $record->request_qty * $record->standard_cost;
                                    })
                                    ->numeric(
                                        decimalPlaces: 0,
                                        decimalSeparator: '.',
                                        thousandsSeparator: ',',
                                    )
                            ])
                            ->columnSpanFull(),
                        RepeatableEntry::make('approvals')
                            ->columns(5)
                            ->schema([
                                TextEntry::make('approval_type')
                                    ->label('Tipe Approval'),
                                TextEntry::make('user.name')
                                    ->label('User'),
                                TextEntry::make('comment'),
                                TextEntry::make('reaction_time'),
                                TextEntry::make('reaction_desc')

                            ])
                            ->columnSpanFull()
                    ])
                    ->columns(3),
            ]);
    }
}
