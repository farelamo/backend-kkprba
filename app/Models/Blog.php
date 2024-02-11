<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'short_title', 'image', 'description', 'is_carousel'];

    public function categories(){
        return $this->belongsToMany(BlogCategory::class, 'blog_categories_relations', 'blog_id', 'blog_category_id');
    }
}
