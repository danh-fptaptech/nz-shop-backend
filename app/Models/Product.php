<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public function comments(): HasMany {
        return $this->hasMany(Product_comment::class);
    }

    public function reviews(): HasMany {
        return $this->hasMany(Review::class);
    }
}
