<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'nom',
        'regiment_id',
        'chef_id',
        'assistant_id',
    ];

    // Un groupe appartient à un régiment
    public function regiment()
    {
        return $this->belongsTo(Regiment::class, 'regiment_id');
    }

    // Le chef du groupe
    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    // L'assistant du groupe
    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    // Les membres du groupe (utilisateurs assignés à ce groupe)
    public function members()
    {
        return $this->hasMany(User::class, 'group_id');
    }

    // Les candidatures pour ce groupe
    public function applications()
    {
        return $this->hasMany(GroupApplication::class, 'group_id');
    }

    // Les activités de ce groupe
    public function activities()
    {
        return $this->hasMany(Activity::class, 'group_id');
    }
}
