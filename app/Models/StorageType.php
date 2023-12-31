<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'max_storage_day', 'min_expired_date'];
}
