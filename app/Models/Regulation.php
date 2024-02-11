<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regulation extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'short_title', 'image', 'description'];

    public function categories(){
        return $this->belongsToMany(RegulationCategory::class, 'regulation_categories_relations', 'regulation_id', 'regulation_category_id');
    }
}
