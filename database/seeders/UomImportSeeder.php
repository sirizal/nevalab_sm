<?php

namespace Database\Seeders;

use App\Imports\UomImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UomImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new UomImport)->import(base_path('database/seeders/uomimport.csv'));
    }
}
