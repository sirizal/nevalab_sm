<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'code',
        'name',
        'description',
        'item_type_id',
        'storage_type_id',
        'uom_id',
        'category_1',
        'category_2',
        'category_3',
        'category_4',
        'active',
        'standard_cost',
        'grammage',
        'sps_file',
        'brand',
        'barcode',
        'short_description',
        'critical_item'
    ];

    public function item_type(): BelongsTo
    {
        return $this->belongsTo(ItemType::class);
    }

    public function storage_type(): BelongsTo
    {
        return $this->belongsTo(StorageType::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function category1(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_1');
    }

    public function category2(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_2');
    }

    public function category3(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_3');
    }

    public function category4(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_4');
    }
}
