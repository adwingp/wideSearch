<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class FAQ extends Model
{
    use HasFactory, Searchable;

    protected $table = 'faqs';
    protected $fillable = ['question', 'answer'];

    public function toSearchableArray()
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
        ];
    }
}
