<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pertanyaan extends Model
{
    use HasFactory;

    protected $table = 'pertanyaans';

    protected $fillable = [
        'tahun_id', 'kategori_id', 'indikator_id',
        'is_parent', 'parent_id', 'level',
        'nomor', 'pertanyaan_kuisioner', 'bobot',
    ];

    // Relasi ke parent
    public function parent()
    {
        return $this->belongsTo(Pertanyaan::class, 'parent_id');
    }

    // Relasi children rekursif (untuk eager loading dalam)
    public function childrenRecursive()
    {
        return $this->hasMany(Pertanyaan::class, 'parent_id')
                    ->orderBy('nomor')
                    ->with('childrenRecursive'); // rekursif
    }

     // Relasi children langsung (1 level)
    public function children()
    {
        return $this->hasMany(Pertanyaan::class, 'parent_id')->orderBy('nomor');
    }

    public function tahun()    { return $this->belongsTo(Tahun::class); }
    public function kategori() { return $this->belongsTo(Kategori::class); }
    public function indikator(){ return $this->belongsTo(Indikator::class); }

    /**
     * Bobot ternormalisasi terhadap bobot indikator.
     * Formula: (bobot_pertanyaan / total_bobot_indikator) * bobot_indikator
     */
    public function getBobotNormalisasiAttribute(): float
    {
        $totalBobot = self::where('indikator_id', $this->indikator_id)
                         ->where('level', 'pertanyaan')
                         ->sum('bobot');

        if ($totalBobot == 0) return 0;

        #return round(($this->bobot / $totalBobot) * $this->indikator->bobot, 4);

        $indikator = $this->relationLoaded('indikator')
            ? $this->indikator
            : $this->indikator()->first();

        if (! $indikator) return 0;

        return round(($this->bobot / $totalBobot) * $indikator->bobot, 4);
    }
}