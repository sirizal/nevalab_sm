<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TallySheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_item_id',
        'pack_qty',
        'pack_uom',
        'qty_per_pack',
        'total_qty',
        'qty_uom',
        'production_date',
        'expire_date',
        'purchase_request_id'
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function purchaseRequestItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class, 'purchase_request_item_id');
    }

    public function packUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'pack_uom');
    }

    public function qtyUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'qty_uom');
    }
}
