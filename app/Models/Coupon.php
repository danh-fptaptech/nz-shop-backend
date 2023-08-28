<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type_coupon',
        'value',
        'type_value',
        'limit_time',
        'date_start',
        'date_end',
        'status',
        'coupon_requests',
    ];
}
