<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegulationCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function regulations(){
        return $this->belongsToMany(Regulation::class, 'regulation_categories_relations', 'regulation_category_id', 'regulation_id');
    }
}
