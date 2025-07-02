<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BahayaLongsorController extends Controller // <-- Namanya sudah diganti
{
    public function longsorData()
    {
        // Path menuju file GeoJSON di folder public
        $path = public_path('geojson/Bahaya longsor.json');

        // Cek jika file tidak ada, kirim error 404
        if (!File::exists($path)) {
            abort(404, "File GeoJSON tidak ditemukan.");
        }

        // Ambil isi file dan kirim sebagai response JSON
        return response()->json(json_decode(File::get($path)));
    }
}