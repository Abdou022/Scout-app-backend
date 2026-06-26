<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $table = 'songs';

    protected $fillable = [
        'titre',
        'paroles',
        'audio_url',
        'categorie',
        'created_by',
    ];

    // La chanson a été créée par un utilisateur (admin)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
