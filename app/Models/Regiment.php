<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regiment extends Model
{
    protected $table = 'regiments';

    protected $fillable = [
        'nom',
        'ville_id',
        'chef_id',
    ];

    // Un régiment appartient à une ville
    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    // Le chef du régiment (un utilisateur)
    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    // Un régiment possède plusieurs groupes
    public function groups()
    {
        return $this->hasMany(Group::class, 'regiment_id');
    }

    // Un régiment possède plusieurs membres (utilisateurs assignés)
    public function users()
    {
        return $this->hasMany(User::class, 'regiment_id');
    }

    // Un régiment possède des événements de type regiment
    public function events()
    {
        return $this->hasMany(Event::class, 'regiment_id');
    }
}
