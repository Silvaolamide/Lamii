<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio', 'date_of_birth', 'gender',
        'is_discoverable', 'discovery_radius', 'onboarding_completed',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'is_discoverable' => 'boolean',
            'onboarding_completed' => 'boolean',
            'discovery_radius' => 'integer',
        ];
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class)->withTimestamps();
    }
}
