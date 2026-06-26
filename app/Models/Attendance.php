<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attendable_type',
        'attendable_id',
        'statut',
        'date_pointage',
    ];

    protected function casts(): array
    {
        return [
            'date_pointage' => 'date',
        ];
    }

    // L'entrée de présence appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation polymorphique : peut pointer vers un Event ou une Activity
    public function attendable()
    {
        return $this->morphTo();
    }
}
