<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_buyer',
        'phone_number_tracking',
        'items',
        'address_shipping',
        'delivery',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function tracking(): HasOne
    {
        return $this->hasOne(Tracking::class);
    }
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
