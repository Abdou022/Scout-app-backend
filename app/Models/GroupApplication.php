<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupApplication extends Model
{
    protected $table = 'group_applications';

    protected $fillable = [
        'user_id',
        'group_id',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // La candidature appartient à un utilisateur (candidat)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // La candidature concerne un groupe
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
