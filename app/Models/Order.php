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

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trackings(): BelongsTo
    {
        return $this->BelongsTo(Tracking::class, 'tracking_id');
    }

    public function transactions(): BelongsTo
    {
        return $this->BelongsTo(Transaction::class, 'transaction_id');
    }
}
