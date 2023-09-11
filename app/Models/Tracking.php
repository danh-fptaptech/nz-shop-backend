<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'deliver',
        'status',
    ];

    public function order(): HasOne
    {
        return $this->HasOne(Order::class,'tracking_id');
    }
}
