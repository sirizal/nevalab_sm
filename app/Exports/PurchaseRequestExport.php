<?php

namespace App\Exports;

use App\Models\VendorItem;
use Illuminate\Support\Carbon;
use App\Models\PurchaseRequest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PurchaseRequestExport implements FromCollection, WithHeadings
{
    public PurchaseRequest $pr;

    public function __construct(PurchaseRequest $pr)
    {
        $this->pr = $pr;
    }

    public function headings(): array
    {
        return [
            'PR ID',
            'PR No',
            'New PR no',
            'Request Date',
            'Request Delivery date',
            'Request Receive date',
            'Vendor Code',
            'Vendor Name',
            'Purchase No',
            'Purchase Date',
            'PO Request Delivery Date',
            'PO Request Receive Date',
            'PR Detail ID',
            'Item No',
            'Description',
            'PR UOM',
            'PO UOM',
            'PO Qty',
            'PO Price',
            'Conversion UOM Qty',
            'Update Flag',
            'New Flag',
            'Delete Flag'
        ];
    }

    public function collection(): Collection
    {
        $collection = collect();

        $prItems = $this->pr->purchaseRequestItems;
        //$vendorItems = VendorItem::where('status', '1')->get();

        foreach ($prItems as $item) {
            if ($this->pr->vendor_id != null) {
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
                'PR ID' => $item->purchase_request_id,
                'PR No' => $item->purchaseRequest->code,
                'New PR no' => '',
                'Request date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->request_date)->format('d/m/Y'),
                'Request Delivery date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->request_delivery_date)->format('d/m/Y'),
                'Request Receive date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->request_receive_date)->format('d/m/Y'),
                'Vendor Code' => $vendorItem->vendor->code ?? '0',
                'Vendor Name' => $vendorItem->vendor->name ?? '0',
                'Purchase No' => $item->purchaseRequest->purchase_no,
                'Purchase Date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->purchase_date ?? '1999-99-99')->format('d/m/Y') ?? '0',
                'PO Request Delivery Date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->request_delivery_date)->format('d/m/Y'),
                'PO Request Receive Date' => Carbon::createFromFormat('Y-m-d', $item->purchaseRequest->request_receive_date)->format('d/m/Y'),
                'PR Detail ID' => $item->id,
                'Item No' => $item->item->code,
                'Description' => $item->item->description,
                'PR UOM' => $item->uom->code,
                'PO UOM' => $vendorItem->uom->code,
                'PO Qty' => $item->request_qty,
                'PO Price' => $vendorItem->price ?? '0',
                'Conversion UOM Qty' => $vendorItem->conversion_qty ?? '1',
                'Update Flag' => '',
                'New Flag' => '',
                'Delete Flag' => ''
            ]);
        }

        //dd($collection);

        return $collection;
    }
}
