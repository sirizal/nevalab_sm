<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\VendorItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class POTemplateUploadExport implements FromCollection, WithHeadings
{
    protected $pr;

    public function __construct()
    {
        $this->pr = PurchaseRequest::where('status', 6)->where('purchase_user', auth()->id())->get();

        //dd($this->pr);
    }

    public function headings(): array
    {
        return [
            'Document No.',
            'Buy From Vendor No.',
            'Order date',
            'Document date',
            'Type',
            'No.',
            'VAT Prod. Posting Grp',
            'Location Code',
            'Description',
            'Variant Code',
            'Quantity',
            'Unit of Measure',
            'Direct Unit Cost excl. TAX',
            'Job No.',
            'Job Task No.',
            'YourReference',
            'COSTCENTER CODE',
            'BRANCH CODE',
            'VENDOR NAME'
        ];
    }


    public function collection(): Collection
    {
        $collection = collect();

        //$prItems = $this->pr->purchaseRequestItems;
        //$vendorItems = VendorItem::where('status', '1')->get();

        foreach ($this->pr as $items) {
            $prItems = $items->purchaseRequestItems;
            foreach ($prItems as $item) {
                if ($items->vendor_id != null) {
                    $vendorItem = VendorItem::where('item_id', $item->item_id)
                        ->where('uom_id', $item->uom_id)
                        ->where('status', '1')
                        ->where('vendor_id', $this->pr->vendor_id)
                        ->orderBy('price', 'asc')
                        ->first();
                } else {
                    $vendorItem = VendorItem::where('item_id', $item->item_id)
                        ->where('uom_id', $item->uom_id)
                        ->where('status', '1')
                        ->orderBy('price', 'asc')
                        ->first();
                }
                $collection->push([
                    'Document No.' => '',
                    'Buy From Vendor No.' => $vendorItem->vendor->code ?? '0',
                    'Order date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->request_date)->format('d/m/Y'),
                    'Document date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->request_date)->format('d/m/Y'),
                    'Type' => $item->purchaseRequest->purchase_type === 'item' ? 'ITEMS' : 'G/L Account',
                    'No.' => $item->purchaseRequest->purchase_type === 'item' ? $item->item->code : $item->gl_no,
                    'VAT Prod. Posting Grp' => '',
                    'Location Code' => $item->purchaseRequest->warehouse->code,
                    'Description' => $item->description,
                    'Variant Code' => $item->variant_code,
                    'Quantity' => $item->request_qty,
                    'Unit of Measure' => $item->uom->code,
                    'Direct Unit Cost excl. TAX' => $vendorItem->price ?? '0',
                    'Job No.' => $item->purchaseRequest->serviceType->job_no,
                    'Job Task No.' => $item->purchaseRequest->serviceType->task_no,
                    'YourReference' => $item->purchaseRequest->code,
                    'COSTCENTER CODE' => '',
                    'BRANCH CODE' => '',
                    'VENDOR NAME' => $vendorItem->vendor->name ?? '0'
                ]);
            }
        }

        //dd($collection);

        return $collection;
    }
}
