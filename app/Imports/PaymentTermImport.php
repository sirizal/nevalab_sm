<?php

namespace App\Imports;

use App\Models\PaymentTerm;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaymentTermImport implements ToModel, WithHeadingRow
{
    use Importable;

    public function model(array $row)
    {
        return new PaymentTerm([
            'code' => $row['code'],
            'payment_days' => $row['payment_days']
        ]);
    }
}
