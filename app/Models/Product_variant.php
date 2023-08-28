<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product_variant extends Model
{
    //use HasFactory;

    protected $fillable = ["value", "sku", "quantity", "origin_price", "sell_price", "discount_price", "product_id"];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
