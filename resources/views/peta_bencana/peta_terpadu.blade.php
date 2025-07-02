@extends('layouts.app')

@section('title', 'Peta Analisis Risiko Komprehensif')

@push('styles')
    {{-- Memanggil library CSS yang dibutuhkan --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    
    <style>
        #map-container { position: relative; }
        #map {
            height: 85vh;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background-color: #e9ecef;
        }

        /* ===== CSS UNTUK SUPER REMOTE CONTROL ===== */
        .leaflet-filter-control {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            max-width: 280px;
        }
        .leaflet-filter-control-toggle {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .leaflet-filter-control-content {
            padding: 15px;
            border-top: 1px solid #eee;
            display: none;
        }
        .leaflet-filter-control.expanded .leaflet-filter-control-content {
            display: block;
        }
        .filter-section h6 {
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
        }
        .form-check-label {
            font-size: 0.9rem;
        }
    </style>
@endpush

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Peta Analisis Risiko Komprehensif</h1>

    <div id="map-container">
        <div id="map"></div>

        <!-- HTML untuk Super Remote Control -->
        <div class="leaflet-filter-control" id="filter-control">
            <div class="leaflet-filter-control-toggle" id="filter-toggle" title="Buka/Tutup Filter">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="leaflet-filter-control-content">
                <div class="filter-section">
                    <h6>Pilih Layer Analisis</h6>
                    {{-- Checkbox untuk setiap layer data --}}
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="risiko" id="cek-risiko">
                        <label class="form-check-label" for="cek-risiko">Indeks Risiko</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="bahaya" id="cek-bahaya">
                        <label class="form-check-label" for="cek-bahaya">Indeks Bahaya</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="kerentanan" id="cek-kerentanan">
                        <label class="form-check-label" for="cek-kerentanan">Indeks Kerentanan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="kapasitas" id="cek-kapasitas">
                        <label class="form-check-label" for="cek-kapasitas">Indeks Kapasitas</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="ikm" id="cek-ikm">
                        <label class="form-check-label" for="cek-ikm">IKM Multi Bencana</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Memanggil library JavaScript yang dibutuhkan --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.11.0/proj4.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4leaflet/1.0.2/proj4leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('map').setView([-7.1, 107.7], 10);
            
            // [PERBAIKAN] Ganti basemap ke OpenStreetMap standar yang lebih stabil
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            
            // [KAMUS LENGKAP] Definisi untuk semua "bahasa" peta yang kita punya
            const crs_utm = new L.Proj.CRS('EPSG:32748', '+proj=utm +zone=48 +south +datum=WGS84 +units=m +no_defs');
            const crs_mercator = new L.Proj.CRS('EPSG:3395', '+proj=merc +lon_0=0 +k=1 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs');

            // "Teknik Ember" untuk setiap layer
            const ember = {
                risiko: L.layerGroup(),
                bahaya: L.layerGroup(),
                kerentanan: L.layerGroup(),
                kapasitas: L.layerGroup(),
                ikm: L.layerGroup()
            };

            // Fungsi master untuk styling, biar warnanya konsisten
            const styleFunction = (feature) => {
                const indeks = feature.properties.Indeks || "Default";
                let warna = "#9E9E9E"; // Abu-abu default
                if (indeks === "Tinggi") warna = "rgba(230, 0, 0, 0.7)";
                else if (indeks === "Sedang") warna = "rgba(255, 153, 0, 0.7)";
                else if (indeks === "Rendah") warna = "rgba(51, 204, 51, 0.7)";
                return { color: "#fff", weight: 0.5, fillColor: warna, fillOpacity: 0.8 };
            };
            
            // Fungsi generik untuk memproses data
            function prosesData(data, namaEmber, crs = null) {
                let layer;
                if (crs) {
                    layer = L.Proj.geoJson(data, { style: styleFunction }, crs);
                } else {
                    layer = L.geoJSON(data, { style: styleFunction });
                }
                
                layer.eachLayer(l => {
                    const props = l.feature.properties;
                    l.bindPopup(`<b>${namaEmber.replace('_', ' ')}</b><br>Indeks: ${props.Indeks}<br>Kecamatan: ${props.WADMKC || 'N/A'}`);
                });

                ember[namaEmber].addLayer(layer);
            }

            // [UPGRADE] Kita fetch 5 data secara terpisah dengan error handling yang jelas
            const dataSources = [
                { name: 'risiko', route: "{{ route('data.risiko') }}", crs: crs_utm },
                { name: 'bahaya', route: "{{ route('data.bahaya') }}", crs: crs_mercator },
                { name: 'kerentanan', route: "{{ route('data.kerentanan') }}", crs: null },
                { name: 'kapasitas', route: "{{ route('data.kapasitas') }}", crs: crs_mercator },
                { name: 'ikm', route: "{{ route('data.ikm') }}", crs: crs_utm }
            ];

            dataSources.forEach(source => {
                fetch(source.route)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Server merespon dengan status ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log(`SUKSES: Data ${source.name} berhasil dimuat.`);
                        prosesData(data, source.name, source.crs);
                    })
                    .catch(err => {
                        console.error(`GAGAL: Tidak bisa memuat data ${source.name}. Error:`, err.message);
                    });
            });

            function updatePeta() {
                document.querySelectorAll('#filter-control .form-check-input').forEach(checkbox => {
                    const layerName = checkbox.value;
                    const targetEmber = ember[layerName];
                    if (targetEmber) {
                        if (checkbox.checked) {
                            if (!map.hasLayer(targetEmber)) map.addLayer(targetEmber);
                        } else {
                            if (map.hasLayer(targetEmber)) map.removeLayer(targetEmber);
                        }
                    }
                });
            }
            
            document.querySelectorAll('#filter-control .form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', updatePeta);
            });

            const filterControl = document.getElementById('filter-control');
            const filterToggle = document.getElementById('filter-toggle');
            filterToggle.addEventListener('click', () => filterControl.classList.toggle('expanded'));
        });
    </script>
@endpush
