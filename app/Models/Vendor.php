<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'pic_name',
        'pic_email',
        'pic_phone',
        'address_1',
        'address_2',
        'address_3',
        'city',
        'tax_no',
        'payment_term_id'
    ];

    public function vendorItems(): HasMany
    {
        return $this->hasMany(VendorItem::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }
}
