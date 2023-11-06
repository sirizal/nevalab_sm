<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Imports\ExcelImport;
use App\Models\Category;
use App\Models\Item;
use App\Models\StorageType;
use App\Models\Uom;
use Filament\Actions\Action;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

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
                    $categories = Category::all();
                    $storageTypes = StorageType::all();

                    foreach ($rows as $row) {
                        $uom = $uoms->where('code', $row['uom'])->first();
                        $category = $categories->where('name', $row['category'])->first();
                        $storageType = $storageTypes->where('name', $row['storage_type'])->first();
                        Item::create([
                            'code' => $row['code'],
                            'name' => $row['name'],
                            'description' => $row['description'],
                            'short_description' => $row['short_description'],
                            'item_type_id' => $row['item_type_id'],
                            'uom_id' => $uom->id ?? 0,
                            'storage_type_id' => $storageType->id ?? 0,
                            'category_1' => $category->id ?? 0,
                            'standard_cost' => $row['standard_cost']
                        ]);
                    }
                })
        ];
    }
}
