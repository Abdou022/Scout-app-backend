<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'nom',
    ];

    // Une catégorie possède plusieurs guides
    public function guides()
    {
        return $this->hasMany(Guide::class, 'category_id');
    }
}
