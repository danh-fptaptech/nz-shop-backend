<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post_feedback extends Model
{
    // use HasFactory;

     public function post_comment() :BelongsTo {
        return $this->belongsTo(Post_comment::class);
    }
}
