<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'job_no',
        'task_no'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
