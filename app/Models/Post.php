<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ["title", "author", "images", "content", "type", "date_created"];

    public function comments(): HasMany
    {
        return $this->hasMany(Post_comment::class);
    }
}
