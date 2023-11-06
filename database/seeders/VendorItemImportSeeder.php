<?php

namespace Database\Seeders;

use App\Imports\VendorItemImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorItemImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new VendorItemImport)->import(base_path('database/seeders/format_upload_vendor_item.csv'));
    }
}
