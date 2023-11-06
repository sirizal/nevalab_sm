<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use LaravelDaily\Invoices\Classes\Party;

class DownloadPdfController extends Controller
{
    public PurchaseRequest $pr;

    public function downloadPr(PurchaseRequest $record)
    {
        $this->pr = $record;

        $prItems = $this->pr->purchaseRequestItems;

        if ($this->pr->process_type == 0) {
            $process_type = 'Reguler';
        } elseif ($this->pr->process_type == 1) {
            $process_type = 'Additional';
        } else {
            $process_type = 'PRA after fact';
        }

        $seller = new Party([
            'custom_fields' => [
                'client' => $this->pr->client->name,
                'site' => $this->pr->site->name,
                'service' => $this->pr->serviceType->name,
                'warehouse' => $this->pr->warehouse->code,
                'category' => $this->pr->category->name,
            ]
        ]);

        $customer = new Buyer([
            'custom_fields' => [
                'process type' => $process_type,
                'tipe produk' => $this->pr->request_type == 0 ? 'Goods' : 'Service',
                'req delivery date' => $this->pr->request_delivery_date,
                'req receive date' => $this->pr->request_receive_date,
                'remarks' => $this->pr->remarks
            ],
        ]);

        $items = array();

        foreach ($prItems as $item) {
            $items[] =
                (new InvoiceItem())
                ->title($item->item->code)
                ->description($item->item->description)
                ->pricePerUnit($item->standard_cost)
                ->quantity($item->request_qty)
                ->units($item->uom->code);
        }

        $invoice = Invoice::make('purchase_request')
            ->name('Purchase Request')
            ->seller($seller)
            ->buyer($customer)
            ->series($this->pr->code)
            ->serialNumberFormat('{SERIES}')
            ->date(Carbon::createFromFormat('Y-m-d', $this->pr->request_date))
            ->dateFormat('d/m/Y')
            ->currencySymbol('Rp')
            ->currencyCode('IDR')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyThousandsSeparator(',')
            ->currencyDecimalPoint('.')
            ->addItems($items)
            ->filename($this->pr->code)
            ->template('prdownload');

        return $invoice->stream();
    }
}
