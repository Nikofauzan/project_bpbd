<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class HistoriBencanaController extends Controller
{
    /**
     * Mengolah data histori bencana dari CSV dan menyajikannya sebagai JSON.
     * Versi ini super cerdas, bisa memetakan header yang kompleks dan membaca
     * file baris per baris untuk efisiensi memori.
     */
    public function data()
    {
        try {
            // Kita kasih "suplemen" memori untuk jaga-jaga jika ada baris yang sangat panjang
            ini_set('memory_limit', '1024M');
            $path = public_path('data/Rekapitulasi Bencana 2025.csv');

            if (!File::exists($path)) {
                Log::error('File HistoriBencanaController tidak ditemukan di path: ' . $path);
                return response()->json(['error' => 'File data histori tidak ditemukan.'], 404);
            }

            // Jurus "Nyendok Satu-satu" dimulai di sini
            $fileHandle = fopen($path, 'r');
            if ($fileHandle === false) {
                Log::error('Gagal membuka file CSV histori.');
                return response()->json(['error' => 'Tidak dapat membuka file data.'], 500);
            }

            // [JURUS MATA ELANG] Cari header yang bener secara dinamis
            $headerMap = []; // Ini buat nyimpen posisi kolom (misal: 'tanggal' ada di kolom ke-1)
            while (($line = fgets($fileHandle)) !== false) {
                // Kita cari baris yang mengandung kata kunci header yang paling mungkin
                if (stripos($line, 'Tanggal') !== false && stripos($line, 'Nama Kecamatan') !== false && stripos($line, 'Dampak Bencana') !== false) {
                    $rawHeaders = str_getcsv(strtolower($line));
                    
                    // [JURUS PEMETAAN CERDAS] Petakan header dari file CSV ke nama standar kita
                    foreach($rawHeaders as $hIndex => $hValue) {
                        $trimmedHeader = trim($hValue);
                        if ($trimmedHeader == 'tanggal') $headerMap['tanggal'] = $hIndex;
                        if ($trimmedHeader == 'nama kecamatan') $headerMap['lokasi'] = $hIndex;
                        if ($trimmedHeader == 'dampak bencana') $headerMap['jenis'] = $hIndex;
                        // Kita ambil 'rumah terdampak' sebagai representasi kerugian
                        if (str_contains($trimmedHeader, 'rumah terdampak')) $headerMap['kerugian'] = $hIndex;
                    }
                    break; // Keluar dari loop setelah header ditemukan
                }
            }

            // Cek apakah header pentingnya ketemu
            if (empty($headerMap) || !isset($headerMap['tanggal']) || !isset($headerMap['lokasi']) || !isset($headerMap['jenis'])) {
                fclose($fileHandle);
                Log::error('Header penting (Tanggal/Nama Kecamatan/Dampak Bencana) tidak ditemukan di CSV.');
                return response()->json(['error' => 'Format CSV tidak dikenali, header penting tidak ditemukan.'], 500);
            }

            // Proses sisa baris data
            $rekapData = [];
            $frekuensiBencana = [];
            $trenBulanan = array_fill(0, 12, 0);
            $frekuensiPerLokasi = [];
            
            // [JURUS NAMPAN SAMPEL] Batasi jumlah data yang masuk ke tabel
            $limitTabel = 100; // Kita cuma akan nampilin 100 baris pertama di tabel
            $barisTabelSaatIni = 0;

            while (($line = fgets($fileHandle)) !== false) {
                $row = str_getcsv($line);
                // Lewati baris kosong atau yang aneh
                if (empty($row) || empty($row[0])) continue;

                // Ambil data berdasarkan pemetaan header yang sudah cerdas
                $jenis = isset($row[$headerMap['jenis']]) ? ucwords(strtolower(trim($row[$headerMap['jenis']]))) : '';
                $lokasi = isset($row[$headerMap['lokasi']]) ? ucwords(strtolower(trim($row[$headerMap['lokasi']]))) : '';
                $tanggal = isset($row[$headerMap['tanggal']]) ? trim($row[$headerMap['tanggal']]) : '';
                $kerugian = isset($headerMap['kerugian']) && isset($row[$headerMap['kerugian']]) ? (int)trim($row[$headerMap['kerugian']]) : 0;

                // Pastikan baris ini adalah data valid
                if (empty($jenis) || empty($lokasi)) continue;

                // Cuma masukin data ke tabel kalau limit belum tercapai
                if ($barisTabelSaatIni < $limitTabel) {
                    $rekapData[] = [
                        'tanggal' => $tanggal,
                        'jenis' => $jenis,
                        'lokasi' => $lokasi,
                        'kerugian' => number_format($kerugian) . ' unit' // Tampilkan sebagai unit rumah
                    ];
                    $barisTabelSaatIni++;
                }

                // Tapi, data untuk diagram TETAP DIHITUNG SEMUA!
                if (!isset($frekuensiBencana[$jenis])) $frekuensiBencana[$jenis] = 0;
                $frekuensiBencana[$jenis]++;

                if (!isset($frekuensiPerLokasi[$jenis])) $frekuensiPerLokasi[$jenis] = [];
                if (!isset($frekuensiPerLokasi[$jenis][$lokasi])) $frekuensiPerLokasi[$jenis][$lokasi] = 0;
                $frekuensiPerLokasi[$jenis][$lokasi]++;

                if (!empty($tanggal)) {
                    $tanggalObj = \DateTime::createFromFormat('d/m/Y', $tanggal) ?: \DateTime::createFromFormat('d-m-Y', $tanggal);
                    if ($tanggalObj) {
                        $bulanIndex = (int)$tanggalObj->format('m') - 1;
                        if (isset($trenBulanan[$bulanIndex])) $trenBulanan[$bulanIndex]++;
                    }
                }
            }
            
            fclose($fileHandle); // Jangan lupa tutup file-nya
            
            arsort($frekuensiBencana);
            foreach ($frekuensiPerLokasi as $jenis => &$lokasiData) {
                arsort($lokasiData);
            }

            return response()->json([
                'tabelData' => $rekapData,
                'frekuensiBencana' => $frekuensiBencana,
                'trenBulanan' => $trenBulanan,
                'frekuensiPerLokasi' => $frekuensiPerLokasi
            ]);

        } catch (\Exception $e) {
            Log::error('Error di HistoriBencanaController: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return response()->json(['error' => 'Terjadi kesalahan di server saat mengolah data.'], 500);
        }
    }
}
