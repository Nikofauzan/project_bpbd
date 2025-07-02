<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse; // Panggil "alat" buat streaming

class RisikoGunungApiController extends Controller
{
    /**
     * Menyediakan data GeoJSON untuk Peta Risiko Gunung Api
     * dengan teknik STREAMING untuk menangani file besar.
     */
    public function gunungApiData()
    {
        try {
            // [PERBAIKAN] Gunakan nama file dan ekstensi yang benar
            $path = public_path('geojson/Indeks_Risiko_Letusan_Gunung_Api.json');

            if (!File::exists($path)) {
                Log::error('[GunungApi] GAGAL: File tidak ditemukan di path: ' . $path);
                return response()->json(['error' => 'File data Gunung Api tidak ditemukan.'], 404);
            }

            // [UPGRADE TOTAL] Kita pake "Jurus Selang" (Streaming) biar ringan
            return new StreamedResponse(function () use ($path) {
                // Buka "keran" ke file
                $fileHandle = fopen($path, 'rb');
                // Langsung "salurkan" isinya ke output
                fpassthru($fileHandle);
                // Tutup "keran"
                fclose($fileHandle);
            }, 200, [
                // Kasih tau browser kalau yang kita kirim ini adalah file JSON
                'Content-Type' => 'application/json',
            ]);

        } catch (\Exception $e) {
            Log::error('[GunungApi] FATAL ERROR: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan fatal di server saat mengambil data Gunung Api.'], 500);
        }
    }
}
