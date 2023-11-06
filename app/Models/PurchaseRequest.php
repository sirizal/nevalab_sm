<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PurchaseRequest extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user', 'id');
    }

    public function managerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user', 'id');
    }

    public function costControllerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cost_controller_user', 'id');
    }

    public function kamUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kam_user', 'id');
    }

    public function purchaseUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchase_user', 'id');
    }

    public function deliveryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_user', 'id');
    }

    public function inspectionUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspection_user', 'id');
    }

    public function receiveUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receive_user', 'id');
    }

    public function erpReceiveUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'erp_receive_user', 'id');
    }

    public function returnUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_user', 'id');
    }

    public function vendorInvoiceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_invoice_user', 'id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function purchaseRequestItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PurchaseRequestApproval::class);
    }

    public function replicateRow()
    {
        $clone = $this->replicate();

        $clone->push();

        foreach ($this->purchaseRequestItems as $item) {
            $clone->purchaseRequestItems()->create($item->toArray());
        }

        foreach ($this->approvals as $approval) {
            $clone->approvals()->create($approval->toArray());
        }

        $clone->save();
    }
}
