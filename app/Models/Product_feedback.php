<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product_feedback extends Model
{
    // use HasFactory;
    public function product_comment() :BelongsTo {
        return $this->belongsTo(Product_comment::class);
    }

}
