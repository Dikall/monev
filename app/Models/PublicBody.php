<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicBody extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_badan',
        'kategori_id',
        'is_registered',
    ];

    protected $casts = [
        'is_registered' => 'boolean',
    ];

    /** User (akun) yang terhubung ke badan publik ini */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function admins()
    {
        return $this->belongsToMany(User::class, 'admin_public_body');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function penilaians()
    {
        return $this->hasMany(Penilaian::class);
    }

    public function jawabans()
    {
        return $this->hasMany(Jawaban::class);
    }
}
