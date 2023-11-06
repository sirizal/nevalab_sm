<?php

namespace App\Filament\Resources\PurchaseRequestResource\Pages;

use App\Models\Uom;
use App\Models\Item;
use App\Models\Site;
use Filament\Actions;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\VendorItem;
use App\Models\ServiceType;
use App\Imports\ExcelImport;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use App\Models\PurchaseRequest;
use App\Models\ItemStandardCost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use App\Models\PurchaseRequestItem;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use App\Exports\POTemplateUploadExport;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;
use App\Exports\PurchaseRequestItemsExport;
use App\Exports\PurchaseRequestTemplateExport;
use App\Filament\Resources\PurchaseRequestResource;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class ListPurchaseRequests extends ListRecords
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        if (auth()->user()->hasRole(['admin', 'PRA admin'])) {
            return [
                Actions\CreateAction::make(),
                Action::make('download_excel')
                    ->label('Format Download')
                    ->form([
                        Section::make()
                            ->schema([
                                Select::make('client_id')
                                    ->label('Klien')
                                    ->options(Client::query()->pluck('code', 'id')->toArray())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(
                                        function ($set) {
                                            $set('site_id', null);
                                            $set('warehouse_id', null);
                                        }
                                    ),
                                Select::make('site_id')
                                    ->label('Site')
                                    ->searchable()
                                    ->required()
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
                                Select::make('warehouse_id')
                                    ->label('Warehouse')
                                    ->searchable()
                                    ->required()
                                    ->options(
                                        function ($get) {
                                            $client = $get('client_id');
                                            if ($client) {
                                                return Warehouse::where('client_id', $client)
                                                    ->get()
                                                    ->pluck('code', 'id')
                                                    ->toArray();
                                            }
                                        }
                                    ),
                                Select::make('service_type_id')
                                    ->label('Service Type')
                                    ->searchable()
                                    ->required()
                                    ->options(
                                        function ($get) {
                                            $client = $get('client_id');
                                            if ($client) {
                                                return ServiceType::where('client_id', $client)
                                                    ->get()
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            }
                                        }
                                    ),
                                Select::make('category_id')
                                    ->label('Category')
                                    ->searchable()
                                    ->required()
                                    ->options(Category::all()->pluck('name', 'id')->toArray()),
                                DatePicker::make('request_date')
                                    ->label('Tanggal Request')
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(
                                        function (callable $set) {
                                            $set('request_delivery_date', null);
                                        }
                                    )
                                    ->closeOnDateSelection(),
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
                                    ->minDate(
                                        function ($get) {
                                            $reqDate = $get('request_date');
                                            if ($reqDate != null) {
                                                return Carbon::parse($reqDate);
                                            }
                                            return null;
                                        }
                                    ),
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
                                    ->closeOnDateSelection(),
                                Select::make('process_type')
                                    ->label('Proses Order')
                                    ->options([
                                        0 => 'Regular',
                                        1 => 'Additional',
                                        2 => 'PRA after fact'
                                    ])
                                    ->required(),
                                Select::make('need_approval_flag')
                                    ->label('Tipe Approval')
                                    ->options([
                                        0 => 'Approval By ISSConnect',
                                        1 => 'Approval By Nevalab'
                                    ])
                                    ->required(),
                                Select::make('vendor_id')
                                    ->label('Supplier')
                                    ->options(Vendor::all()->pluck('name', 'id')->toArray())
                                    ->searchable()
                            ])->columns(3)

                    ])
                    ->action(
                        function (array $data) {
                            return Excel::download(
                                new PurchaseRequestTemplateExport($data),
                                'purchaserequesttemplate_' . time() . '.xlsx',
                                \Maatwebsite\Excel\Excel::XLSX
                            );
                        }
                    ),
                Action::make('excel_import')
                    ->label('Import From Excel')
                    ->form([
                        FileUpload::make('excel_file')
                            ->required()
                            ->preserveFilenames()
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ])
                    ->action(function (array $data) {
                        $rows = Excel::toCollection(new ExcelImport, new UploadedFile(Storage::disk('public')->path($data['excel_file']), $data['excel_file']))->flatten(1);
                        //dd($rows);
                        $headers = $rows->unique('no', 'need_approval_flag', 'request_date', 'request_delivery_date', 'request_receive_date', 'client_id', 'service_type_id', 'warehouse_id',  'purchase_type', 'request_type', 'process_type', 'inspection_status', 'remarks', 'site_id', 'category_id', 'vendor_id')->values();

                        //dd($headers);

                        $items = Item::all();
                        $uoms = Uom::all();
                        $itemStandardCost = ItemStandardCost::all();
                        $categories = Category::all();

                        foreach ($headers as $header) {
                            if ($header['no'] != null) {
                                $code = make_purchase_request_no();
                                $categoryUser = $categories->where('id', $header['category_id'])->first();
                                $purchaseRequest = PurchaseRequest::create([
                                    'code' => $code,
                                    // 'request_date' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($header['request_date'])),
                                    // 'request_delivery_date' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($header['request_delivery_date'])),
                                    // 'request_receive_date' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($header['request_receive_date'])),
                                    'status' => $header['need_approval_flag'] === 0 ? 6 : 0,
                                    'request_date' => $header['request_date'],
                                    'request_delivery_date' => $header['request_delivery_date'],
                                    'request_receive_date' => $header['request_receive_date'],
                                    'client_id' => $header['client_id'],
                                    'service_type_id' => $header['service_type_id'],
                                    'warehouse_id' => $header['warehouse_id'],
                                    'purchase_type' => $header['purchase_type'],
                                    'remarks' => $header['remarks'],
                                    'create_user' => auth()->id(),
                                    'request_type' => $header['request_type'],
                                    'process_type' => $header['process_type'],
                                    'site_id' => $header['site_id'],
                                    'category_id' => $header['category_id'],
                                    'purchase_user' => $categoryUser->user_id,
                                    'inspection_status' => $header['inspection_status'],
                                    'vendor_id' => $header['vendor_id'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                                $filter_data = $rows->where('no', $header['no'])->all();

                                foreach ($filter_data as $filter) {
                                    $item = $items->where('id', $filter['item_id'])->first();
                                    $prItem = $itemStandardCost->where('item_id', $filter['item_id'])->where('site_id', $header['site_id'])->whereNull('end_date')->first();
                                    $uom = $uoms->where('code', $filter['uom'])->first();
                                    if ($header['purchase_type'] === 'item') {
                                        PurchaseRequestItem::create([
                                            'purchase_request_id' => $purchaseRequest->id,
                                            'item_id' => $filter['item_id'],
                                            'description' => $item->name ?? NULL,
                                            'uom_id' => $item->uom_id ?? NULL,
                                            'standard_cost' => $prItem->standard_cost ?? '0',
                                            'request_qty' => $filter['request_qty'] ?? '0'
                                        ]);
                                    } else {
                                        PurchaseRequestItem::create([
                                            'purchase_request_id' => $purchaseRequest->id,
                                            'gl_no' => $filter['gl_no'],
                                            'description' => $filter['description'],
                                            'uom_id' => $uom->id,
                                            'standard_cost' => $filter['standard_cost'],
                                            'request_qty' => $filter['request_qty'] ?? '0'
                                        ]);
                                    }
                                }
                            }
                        }
                    })
            ];
        } elseif (auth()->user()->hasRole(['admin', 'buyer'])) {
            return [
                Action::make('export')
                    ->label('Download PO template All')
                    ->action(fn ($record) => Excel::download(
                        new POTemplateUploadExport(),
                        'potemplate_' . time() . '.xlsx',
                        \Maatwebsite\Excel\Excel::XLSX
                    ))
            ];
        }
        return [];
    }

    public function getTabs(): array
    {
        if (auth()->user()->hasRole('buyer')) {
            return [
                'Ready for PO' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 6)->where('purchase_user', auth()->id())),
                'Waiting Delivery' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 8)->where('purchase_user', auth()->id())),
            ];
        }
        return [
            null => ListRecords\Tab::make('All'),
            'Ready for PO' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 6)),
            'Waiting Delivery' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 8)),
        ];
    }
}
