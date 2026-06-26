<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'profile_pic',
        'role',
        'ville_id',
        'regiment_id',
        'group_id',
        'grade_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // L'utilisateur appartient à une ville
    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    // L'utilisateur appartient à un régiment (nullable)
    public function regiment()
    {
        return $this->belongsTo(Regiment::class, 'regiment_id');
    }

    // L'utilisateur appartient à un groupe (nullable)
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    // L'utilisateur a un grade (nullable)
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    // Candidatures soumises par cet utilisateur
    public function applications()
    {
        return $this->hasMany(GroupApplication::class, 'user_id');
    }

    // Présences (polymorphiques) de cet utilisateur
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    // Vérifier si l'utilisateur est admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Vérifier si l'utilisateur est chef de régiment
    public function isChefRegiment(): bool
    {
        return $this->role === 'chef_regiment';
    }

    // Vérifier si l'utilisateur est chef de groupe
    public function isChefGroupe(): bool
    {
        return $this->role === 'chef_groupe';
    }

    // Vérifier si l'utilisateur est candidat
    public function isCandidat(): bool
    {
        return $this->role === 'candidat';
    }
}
