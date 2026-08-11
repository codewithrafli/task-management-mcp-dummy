<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    protected $fillable = ['title', 'source', 'content', 'embedding'];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }
}
