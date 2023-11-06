<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'approval_type',
        'user_id',
        'comment',
        'reaction_time',
        'reaction_desc'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
