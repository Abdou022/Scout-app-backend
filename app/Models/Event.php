<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'lieu',
        'latitude',
        'longitude',
        'cover_image',
        'type',
        'ville_id',
        'regiment_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin'   => 'datetime',
            'latitude'   => 'float',
            'longitude'  => 'float',
        ];
    }

    // L'événement appartient à une ville
    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    // L'événement peut appartenir à un régiment (nullable, si type=regiment)
    public function regiment()
    {
        return $this->belongsTo(Regiment::class, 'regiment_id');
    }

    // L'événement a été créé par un utilisateur
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Les présences à cet événement (relation polymorphique inverse via attendable)
    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }
}
