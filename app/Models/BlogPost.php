<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class BlogPost extends Model
{
    use HasFactory, Searchable;

    protected $fillable = ['title', 'body', 'tags', 'published_at'];

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'tags' => $this->tags,
            'published_at' => $this->published_at,
        ];
    }
}
