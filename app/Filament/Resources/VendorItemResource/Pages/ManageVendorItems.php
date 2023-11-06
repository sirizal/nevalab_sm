<?php

namespace App\Filament\Resources\VendorItemResource\Pages;

use App\Models\Uom;
use App\Models\Item;
use Filament\Actions;
use App\Models\Vendor;
use App\Models\VendorItem;
use App\Imports\ExcelImport;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\VendorItemResource;

class ManageVendorItems extends ManageRecords
{
    protected static string $resource = VendorItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('import_excel')
                ->label('Excel Import')
                ->steps([
                    Step::make('download excel format')
                        ->description('Silahkan download excel format berikut')
                        ->schema([
                            Placeholder::make('')
                                ->content(new HtmlString('text for download'))
                                ->helperText('Silahkan download format excel berikut untuk melakukan proses import data')
                        ]),
                    Step::make('upload excel')
                        ->description('Silahkan upload excel format yang sudah di isi')
                        ->schema([
                            FileUpload::make('excel_file')
                                ->required()
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ])
                ])
                ->action(function (array $data) {
                    $rows = Excel::toCollection(new ExcelImport, new UploadedFile(Storage::disk('public')->path($data['excel_file']), $data['excel_file']))->flatten(1);

                    $uoms = Uom::all();
                    $items = Item::all();
                    $vendors = Vendor::all();

                    foreach ($rows as $row) {
                        $uom = $uoms->where('code', $row['uom'])->first();
                        $item = $items->where('code', $row['item_no'])->first();
                        $vendor = $vendors->where('code', $row['vendor_code'])->first();
                        VendorItem::create([
                            'vendor_id' => $vendor->id ?? 0,
                            'item_id' => $item->id ?? 0,
                            'price' => $row['price'],
                            'uom_id' => $uom->id ?? 0,
                            'start_date' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date'])),
                            'end_date' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['end_date'])),
                        ]);
                    }
                })
        ];
    }
}
