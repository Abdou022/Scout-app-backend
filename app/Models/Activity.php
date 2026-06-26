<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';

    protected $fillable = [
        'group_id',
        'titre',
        'programme',
        'date',
        'lieu',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    // L'activité appartient à un groupe
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    // L'activité a été créée par un utilisateur
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Les présences à cette activité (relation polymorphique)
    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }
}
