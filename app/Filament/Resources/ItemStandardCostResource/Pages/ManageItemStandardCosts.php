<?php

namespace App\Filament\Resources\ItemStandardCostResource\Pages;

use App\Models\Site;
use Filament\Actions;
use App\Models\Client;
use App\Imports\ExcelImport;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use App\Models\ItemStandardCost;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\ManageRecords;
use App\Exports\ItemStandardCostTemplateExport;
use App\Filament\Resources\ItemStandardCostResource;

class ManageItemStandardCosts extends ManageRecords
{
    protected static string $resource = ItemStandardCostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('download_excel')
                ->label('Format Download')
                ->form([
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
                ])
                ->action(
                    function (array $data) {
                        return Excel::download(
                            new ItemStandardCostTemplateExport($data),
                            'itemstandardcosttemplate_' . time() . '.xlsx',
                            \Maatwebsite\Excel\Excel::XLSX
                        );
                    }
                ),
            Action::make('upload_excel')
                ->label('Excel upload')
                ->form([
                    FileUpload::make('excel_file')
                        ->required()
                        ->preserveFilenames()
                        ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ])
                ->action(function (array $data) {
                    $rows = Excel::toCollection(new ExcelImport, new UploadedFile(Storage::disk('public')->path($data['excel_file']), $data['excel_file']))->flatten(1);

                    foreach ($rows as $row) {
                        ItemStandardCost::create([
                            'client_id' => $row['client_id'],
                            'site_id' => $row['site_id'],
                            'item_id' => $row['item_id'],
                            'standard_cost' => $row['standard_cost'],
                            'start_date' =>  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date'])),
                            'end_date' => $row['end_date']
                        ]);
                    }
                })

        ];
    }
}
