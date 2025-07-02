@extends('layouts.app')

@section('title', 'Peta Bahaya Gempa Bumi')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        #map-container {
            position: relative;
        }

        #map {
            height: 80vh;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* ===== CSS UNTUK REMOTE CONTROL FILTER ===== */
        .leaflet-filter-control {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            max-width: 250px;
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
    <h1 class="h3 mb-4 text-gray-800">Peta Interaktif Bahaya Gempa Bumi</h1>

    <div id="map-container">
        <div id="map"></div>

        <!-- HTML UNTUK REMOTE CONTROL FILTER -->
        <div class="leaflet-filter-control" id="filter-control">
            <div class="leaflet-filter-control-toggle" id="filter-toggle" title="Buka/Tutup Filter">
                <i class="fas fa-filter"></i>
            </div>
            <div class="leaflet-filter-control-content">
                <div class="filter-section">
                    <h6>Filter Risiko Bencana</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Tinggi" id="filter-tinggi" checked>
                        <label class="form-check-label" for="filter-tinggi">
                            <i class="fas fa-circle me-2" style="color: #d32f2f;"></i> Risiko Tinggi
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Sedang" id="filter-sedang" checked>
                        <label class="form-check-label" for="filter-sedang">
                            <i class="fas fa-circle me-2" style="color: #f57c00;"></i> Risiko Sedang
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Rendah" id="filter-rendah" checked>
                        <label class="form-check-label" for="filter-rendah">
                            <i class="fas fa-circle me-2" style="color: #33cc33";></i> Risiko Rendah
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.11.0/proj4.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4leaflet/1.0.2/proj4leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('map').setView([-7.25, 107.75], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const crs_utm_def = '+proj=utm +zone=48 +south +datum=WGS84 +units=m +no_defs';
            const crs_utm = new L.Proj.CRS('EPSG:32748', crs_utm_def, {
                resolutions: [16384, 8192, 4096, 2048, 1024, 512, 256, 128],
                origin: [0, 10000000]
            });

            // Pake "Teknik Ember" (LayerGroup)
            const ember = {
                Tinggi: L.layerGroup(),
                Sedang: L.layerGroup(),
                Rendah: L.layerGroup(),
            };

            fetch("{{ route('peta.gempabumi.data') }}")
                .then(response => {
                    if (!response.ok) throw new Error(`Gagal ambil data (Error: ${response.status})`);
                    return response.json();
                })
                .then(data => {
                    if (!data.features || data.features.length === 0) {
                        console.error("Data GeoJSON kosong atau formatnya salah.");
                        return;
                    }

                    L.Proj.geoJson(data, {
                        onEachFeature: function (feature, layer) {
                            let risiko = feature.properties.Indeks;

                            if (ember[risiko]) {
                                const popupContent = `
                                        <b>KRB Gempa Bumi</b><br>
                                        Level Risiko: <strong>${risiko}</strong>
                                        <hr style="margin: 5px 0;">
                                        <a href="https://youtu.be/xgSp2FppSyA?si=u7sLAlXNl5hD8w0x" target="_blank">Apa itu Gempa?</a>
                                    `;
                                layer.bindPopup(popupContent);
                                ember[risiko].addLayer(layer);
                            }
                        },
                        style: function (feature) {
                            let risiko = feature.properties.Indeks;
                            let warna = "#cccccc"; // abu-abu
                            if (risiko === "Tinggi") warna = "#d32f2f";
                            else if (risiko === "Sedang") warna = "#f57c00";
                            else if (risiko === "Rendah") warna = "#33cc33";
                            return { color: "#000", weight: 1, fillColor: warna, fillOpacity: 0.7 };
                        }
                    });

                    Object.values(ember).forEach(e => e.addTo(map));

                })
                .catch(err => console.error('Gagal load GeoJSON:', err));

            // Logika untuk Remote Control Filter
            function updatePeta() {
                document.querySelectorAll('.form-check-input').forEach(checkbox => {
                    const risikoValue = checkbox.value;
                    const targetEmber = ember[risikoValue];
                    if (targetEmber) {
                        if (checkbox.checked) map.addLayer(targetEmber);
                        else map.removeLayer(targetEmber);
                    }
                });
            }

            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', updatePeta);
            });

            const filterControl = document.getElementById('filter-control');
            const filterToggle = document.getElementById('filter-toggle');
            filterToggle.addEventListener('click', () => filterControl.classList.toggle('expanded'));
        });
    </script>
@endpush