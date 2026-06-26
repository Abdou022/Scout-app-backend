<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    protected $table = 'guides';

    protected $fillable = [
        'titre',
        'contenu_html',
        'cover_image',
        'category_id',
        'created_by',
    ];

    // Un guide appartient à une catégorie
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Le guide a été créé par un utilisateur (admin)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
