<?php

namespace App\Observers;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;

class PurchaseRequestItemObserver
{
    /**
     * Handle the PurchaseRequestItem "created" event.
     */
    public function created(PurchaseRequestItem $purchaseRequestItem): void
    {
        $this->calculateTotal($purchaseRequestItem);
    }

    /**
     * Handle the PurchaseRequestItem "updated" event.
     */
    public function updated(PurchaseRequestItem $purchaseRequestItem): void
    {
        $this->calculateTotal($purchaseRequestItem);
    }

    /**
     * Handle the PurchaseRequestItem "deleted" event.
     */
    public function deleted(PurchaseRequestItem $purchaseRequestItem): void
    {
        $this->calculateTotal($purchaseRequestItem);
    }

    /**
     * Handle the PurchaseRequestItem "restored" event.
     */
    public function restored(PurchaseRequestItem $purchaseRequestItem): void
    {
        //
    }

    /**
     * Handle the PurchaseRequestItem "force deleted" event.
     */
    public function forceDeleted(PurchaseRequestItem $purchaseRequestItem): void
    {
        //
    }

    private function calculateTotal(PurchaseRequestItem $purchaseRequestItem)
    {
        $requestQty = PurchaseRequestItem::where('purchase_request_id', $purchaseRequestItem->purchase_request_id)->sum('request_qty');

        $requestAmount = PurchaseRequestItem::where('purchase_request_id', $purchaseRequestItem->purchase_request_id)->sum(\DB::raw('request_qty*standard_cost'));

        $purchaseRequest = PurchaseRequest::find($purchaseRequestItem->purchase_request_id);
        $purchaseRequest->total_request_qty = $requestQty;
        $purchaseRequest->total_request_amount = $requestAmount;

        $purchaseRequest->update();
    }
}
