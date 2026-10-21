<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description', 'is_published', 'show_in_footer'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'show_in_footer' => 'boolean'];
    }
}
