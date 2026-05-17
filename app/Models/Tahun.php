<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tahun extends Model
{
    use HasFactory;

    protected $table = 'tahuns';
    
    protected $fillable = ['tahun',
                            'bobot_saq', 
                            'bobot_presentasi'];

    public function kategoris()
    {
        return $this->hasMany(Kategori::class, 'tahun_id');
    }

    public function indikators()
    {
        return $this->hasMany(Indikator::class, 'tahun_id');
    }

    public function pertanyaans()
    {
        return $this->hasMany(Pertanyaan::class, 'tahun_id');
    }

    public function jawabans()
    {
        return $this->hasMany(Jawaban::class, 'tahun_id');
    }
}