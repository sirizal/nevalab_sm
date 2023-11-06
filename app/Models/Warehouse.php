<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'code',
        'name',
        'address1',
        'address2',
        'address3',
        'city',
        'postal_code'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
