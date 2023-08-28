<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product_comment extends Model
{
     //use HasFactory;

    protected $fillable = ["comment", "user_id", "product_id"];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    
    public function product_feedbacks(): HasMany {
        return $this->hasMany(Product_feedback::class);
    }
}
