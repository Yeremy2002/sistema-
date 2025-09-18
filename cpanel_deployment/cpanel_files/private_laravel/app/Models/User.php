<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean'
    ];

    /**
     * Get the default profile image URL.
     *
     * @return string
     */
    public function adminlte_image()
    {
        return 'https://picsum.photos/300/300';
    }

    /**
     * Get the default profile description.
     *
     * @return string
     */
    public function adminlte_desc()
    {
        return 'Administrador';
    }

    /**
     * Get the default profile URL.
     *
     * @return string|null
     */
    public function adminlte_profile_url()
    {
        return route('perfil.edit');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
