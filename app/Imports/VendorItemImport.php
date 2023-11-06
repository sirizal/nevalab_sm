<?php

namespace App\Imports;

use App\Models\Uom;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\VendorItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VendorItemImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $uoms;
    protected $items;
    protected $vendors;

    public function __construct()
    {
        $this->uoms = Uom::select('id', 'code')->get();
        $this->items = Item::select('id', 'code')->get();
        $this->vendors = Vendor::select('id', 'code')->get();
    }

    public function model(array $row)
    {
        $uom = $this->uoms->where('code', $row['uom'])->first();
        $item = $this->items->where('code', $row['item_no'])->first();
        $vendor = $this->vendors->where('code', $row['vendor_code'])->first();

        return new VendorItem([
            'vendor_id' => $vendor->id ?? 0,
            'item_id' => $item->id ?? 0,
            'price' => $row['price'],
            'uom_id' => $uom->id ?? 0,
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ]);
    }
}
