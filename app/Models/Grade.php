<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';

    protected $fillable = [
        'nom',
        'niveau',
        'image',
    ];

    // Un grade peut être associé à plusieurs utilisateurs
    public function users()
    {
        return $this->hasMany(User::class, 'grade_id');
    }
}
