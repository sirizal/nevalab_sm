<?php

namespace Database\Seeders;

use App\Imports\PaymentTermImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTermImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PaymentTermImport)->import(base_path('database/seeders/paymenttermimport.csv'));
    }
}
