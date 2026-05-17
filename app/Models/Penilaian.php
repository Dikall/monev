<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaians';

    protected $fillable = [
        'public_body_id',
        'tahun_id',
        'nilai_presentasi',
        'file_bukti_presentasi',
        'is_published',
        'tanggal_publish',
        'catatan',
    ];

    protected $casts = [
        'nilai_presentasi' => 'decimal:2',
        'is_published'     => 'boolean',
        'tanggal_publish'   => 'datetime',
    ];

    public function publicBody()
    {
        return $this->belongsTo(PublicBody::class);
    }

    public function tahun()
    {
        return $this->belongsTo(Tahun::class);
    }
}
