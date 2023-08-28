<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $fillable = ["name", "post_id"];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
