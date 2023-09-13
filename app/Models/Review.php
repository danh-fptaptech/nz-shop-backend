<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
     protected $fillable = ["rating", "comment", "user_id", "product_id"];
    //use HasFactory;
    public function user()
{
    return $this->belongsTo(User::class);
}
}

