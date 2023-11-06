<?php

namespace App\Filament\Resources\VendorResource\Pages;

use Filament\Actions;
use App\Models\Vendor;
use App\Models\PaymentTerm;
use App\Imports\ExcelImport;
use Filament\Actions\Action;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\VendorResource;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

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

                    $paymentTerms = PaymentTerm::all();

                    foreach ($rows as $row) {
                        $paymentTerm = $paymentTerms->where('code', $row['payment_terms_code'])->first();
                        Vendor::create([
                            'code' => $row['no'],
                            'name' => $row['name'],
                            'pic_name' => $row['contact'],
                            'pic_email' => $row['email'],
                            'pic_phone' => $row['phone_no'],
                            'tax_no' => $row['tax_no'],
                            'address_1' => $row['address'],
                            'city' => $row['city'],
                            'payment_term_id' => $paymentTerm->id ?? 0
                        ]);
                    }
                })
        ];
    }
}
