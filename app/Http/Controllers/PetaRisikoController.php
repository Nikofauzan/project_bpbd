<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// [UPGRADE] Kita cuma butuh satu Controller ini buat ngurusin semua!
class PetaRisikoController extends Controller
{
    /**
     * Menyediakan SATU data GeoJSON "final" yang sudah "disuntik"
     * dengan informasi dari layer lain.
     */
    public function data()
    {
        // [JURUS SAKTI #1] Kasih "Suplemen" ke PHP biar lebih kuat
        // Ini bakal naikin batas memori PHP jadi 1GB.
        // Hapus atau kecilkan jika di server hosting ada batasan.
        ini_set('memory_limit', '1024M');

        try {
            // Definisikan path ke semua "bahan mentah" kita
            // [PERBAIKAN] Pastikan ekstensinya .geojson sesuai nama file aslimu
            $basePath = public_path('Geojson/Multi Bencana');
            $paths = [
                'risiko'     => $basePath . '/Indeks_Risiko_Multi Bencana.geojson',
                'bahaya'     => $basePath . '/Indeks_Bahaya_Multi Bencana.geojson',
                'kerentanan' => $basePath . '/Indeks_Kerentanan_Multi Bencana.geojson',
                'kapasitas'  => $basePath . '/Indeks_Kapasitas_Multi Bencana.geojson',
                'ikm'        => $basePath . '/IKM_Multi Bencana.geojson',
            ];

            // 1. Baca semua file dan ubah jadi objek PHP
            $data = [];
            foreach ($paths as $key => $path) {
                if (!File::exists($path)) {
                    Log::error("File untuk '$key' tidak ditemukan di: $path");
                    // Jika ada file penting yang hilang, langsung gagalkan proses
                    if ($key === 'risiko') {
                        return response()->json(['error' => "File data utama (Risiko) tidak ditemukan."], 500);
                    }
                    // Untuk data pendukung, kita buat objek kosong
                    $data[$key] = json_decode('{"type": "FeatureCollection", "features": []}');
                } else {
                    $fileContent = File::get($path);
                    $data[$key] = json_decode($fileContent);
                    // [JURUS SAKTI #2] Cek apakah JSON valid setelah dibaca
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error("Isi file '$key' bukan JSON yang valid. Error: " . json_last_error_msg());
                        return response()->json(['error' => "Gagal memproses file '$key', format tidak valid."], 500);
                    }
                }
            }

            // 2. Buat "Kamus" biar pencarian cepat
            $kamus = [];
            foreach (['bahaya', 'kerentanan', 'kapasitas', 'ikm'] as $key) {
                if (isset($data[$key]->features)) {
                    foreach ($data[$key]->features as $feature) {
                        if (isset($feature->properties->WADMKC) && isset($feature->properties->Indeks)) {
                            $kecamatan = trim($feature->properties->WADMKC);
                            $indeks = trim($feature->properties->Indeks);
                            // Bikin satu entri untuk setiap kecamatan
                            $kamus[$key][$kecamatan] = $indeks;
                        }
                    }
                }
            }

            // 3. "Suntik" data ke Peta Risiko
            if (isset($data['risiko']->features)) {
                foreach ($data['risiko']->features as $feature) {
                    $kecamatan = isset($feature->properties->WADMKC) ? trim($feature->properties->WADMKC) : null;

                    if ($kecamatan) {
                        // Ambil data dari kamus, suntikkan sebagai properti baru
                        $feature->properties->BAHAYA = $kamus['bahaya'][$kecamatan] ?? 'N/A';
                        $feature->properties->KERENTANAN = $kamus['kerentanan'][$kecamatan] ?? 'N/A';
                        $feature->properties->KAPASITAS = $kamus['kapasitas'][$kecamatan] ?? 'N/A';
                        $feature->properties->IKM = $kamus['ikm'][$kecamatan] ?? 'N/A';
                    }
                }
            }

            // 4. Kirim SATU GeoJSON "final" yang udah komplit
            Log::info('[PetaRisiko] SUKSES: Data komprehensif berhasil dibuat dan dikirim.');
            return response()->json($data['risiko']);

        } catch (\Exception $e) {
            Log::error('[PetaRisiko] FATAL ERROR: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return response()->json(['error' => 'Terjadi kesalahan fatal di server.'], 500);
        }
    }
}
