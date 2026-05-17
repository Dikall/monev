<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'public_body_id',
        'name',
        'username',
        'alamat',
        'telepon',
        'website',
        'email',
        'password',

        'nama_responden',
        'jabatan_responden',
        'nohp_responden',
        'email_responden',

        'nama_ppid',
        'nohp_ppid',
        'email_ppid',

        'is_aktif',
        'type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // untuk badan publik
    public function publicBody()
    {
        return $this->belongsTo(PublicBody::class);
    }

    // untuk admin
    public function publicBodies()
    {
        return $this->belongsToMany(PublicBody::class, 'admin_public_body');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
