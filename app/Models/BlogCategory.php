<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function blogs(){
        return $this->belongsToMany(Blog::class, 'blog_categories_relations', 'blog_category_id', 'blog_id');
    }
}
