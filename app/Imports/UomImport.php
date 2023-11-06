<?php

namespace App\Imports;

use App\Models\Uom;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UomImport implements ToModel, WithHeadingRow
{
    use Importable;

    public function model(array $row)
    {
        return new Uom([
            'code' => $row['code'],
            'name' => $row['name']
        ]);
    }
}
