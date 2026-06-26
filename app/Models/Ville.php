<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $table = 'villes';

    protected $fillable = [
        'nom',
    ];

    // Une ville possède plusieurs régiments
    public function regiments()
    {
        return $this->hasMany(Regiment::class, 'ville_id');
    }

    // Une ville possède plusieurs utilisateurs
    public function users()
    {
        return $this->hasMany(User::class, 'ville_id');
    }

    // Une ville possède plusieurs événements
    public function events()
    {
        return $this->hasMany(Event::class, 'ville_id');
    }
}
