<?php

namespace App\Http\Controllers;

use App\Models\GeoArea;
use Illuminate\Http\Request;

class GeoAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('welcome')->layout('components.layouts.app');
    }

    public function getGeoJson()
    {
        $areas = GeoArea::all();

        $geojson = $areas->map(function ($area) {
            $geo = json_decode($area->geojson, true);
            $geo['properties'] = [
                'name' => $area->name,
                'color' => $area->color,
                'kategori' => $area->kategori ?? 'default', // Assuming 'kategori' is a field in the GeoArea model
            ];
            return $geo;
        });

        return response()->json($geojson);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'geojson' => 'required|json',
            'color' => 'required|string',
            'kategori' => 'nullable|string', // Assuming 'kategori' is optional
        ]);

        GeoArea::create([
            'name' => $request->name,
            'geojson' => $request->geojson,
            'color' => $request->color,
            'kategori' => $request->kategori ?? 'default', // Default value if 'kategori' is not provided
        ]);

        return response()->json(['message' => 'Area saved successfully']);
    }

    public function destroy($id)
{
    $area = GeoArea::findOrFail($id);
    $area->delete();

    return response()->json(['message' => 'Area berhasil dihapus']);
}

public function update(Request $request, $id)
{
    $area = GeoArea::findOrFail($id);

    $request->validate([
        'name' => 'required|string',
        'geojson' => 'required|json',
        'color' => 'required|string',
        'kategori' => 'nullable|string',
    ]);

    $area->update([
        'name' => $request->name,
        'geojson' => $request->geojson,
        'color' => $request->color,
        'kategori' => $request->kategori ?? 'default',
    ]);

    return response()->json(['message' => 'Area berhasil diperbarui']);
}

}

