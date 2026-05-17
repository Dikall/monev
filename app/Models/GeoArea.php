<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoArea extends Model
{
    protected $table = 'geo_areas';

    protected $fillable = ['name', 'geojson', 'color', 'kategori'];

    protected $casts = [
        'geojson' => 'array',
    ];
}
