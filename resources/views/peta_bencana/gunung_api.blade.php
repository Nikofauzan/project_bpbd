@extends('layouts.app')

@section('title', 'Peta Bahaya Gunung Api')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        #map-container {
            position: relative;
        }
        #map {
            height: 70vh;
            border-radius: 10px;
        }
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
    <h1 class="h3 mb-4 text-gray-800">Peta Interaktif Bahaya Gunung Api</h1>

    <div id="map-container">
        <div id="map"></div>

        <div class="leaflet-filter-control" id="filter-control">
            <div class="leaflet-filter-control-toggle" id="filter-toggle" title="Buka/Tutup Filter">
                <i class="fas fa-filter"></i>
            </div>
            <div class="leaflet-filter-control-content">
                <div class="filter-section">
                    <h6>Filter Atribut</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Tinggi" id="filter-tinggi" checked>
                        <label class="form-check-label" for="filter-tinggi">
                            <i class="fas fa-circle me-2" style="color: #e60000;"></i> Risiko Tinggi
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Sedang" id="filter-sedang" checked>
                        <label class="form-check-label" for="filter-sedang">
                            <i class="fas fa-circle me-2" style="color: #ff9900;"></i> Risiko Sedang
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Rendah" id="filter-rendah" checked>
                        <label class="form-check-label" for="filter-rendah">
                            <i class="fas fa-circle me-2" style="color: #33cc33;"></i> Risiko Rendah
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
            var map = L.map('map').setView([-7.1, 107.65], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Siapin kamus buat nerjemahin data UTM
            const crs_utm = new L.Proj.CRS('EPSG:32748', '+proj=utm +zone=48 +south +datum=WGS84 +units=m +no_defs');

            const ember = {
                Tinggi: L.layerGroup(),
                Sedang: L.layerGroup(),
                Rendah: L.layerGroup(),
            };

            fetch('{{ route('peta.gunungapi.data') }}')
                .then(response => response.json())
                .then(data => {
                    // [PERBAIKAN] Pake L.Proj.geoJson karena datanya UTM
                    L.Proj.geoJson(data, {
                        onEachFeature: function (feature, layer) {
                            // [PERBAIKAN] Ganti REMARK jadi Indeks
                            let risiko = feature.properties.Indeks;
                            
                            if (ember[risiko]) {
                                let nama = feature.properties.NAMOBJ || 'N/A';
                                let popupContent = `<b>Wilayah:</b> ${nama}<br><b>Status Risiko:</b> ${risiko}`;
                                layer.bindPopup(popupContent);
                                ember[risiko].addLayer(layer);
                            }
                        },
                        style: function (feature) {
                            // [PERBAIKAN] Ganti REMARK jadi Indeks
                            let risiko = feature.properties.Indeks;
                            let warna = "#cccccc";
                            if (risiko === "Tinggi") warna = "#e60000";
                            else if (risiko === "Sedang") warna = "#ff9900";
                            else if (risiko === "Rendah") warna = "#33cc33";
                            return { color: "#000", weight: 1, fillColor: warna, fillOpacity: 0.7 };
                        }
                    }, crs_utm); // Jangan lupa kasih kamusnya

                    Object.values(ember).forEach(e => e.addTo(map));
                })
                .catch(err => console.error('Gagal load GeoJSON:', err));

            function updatePeta() {
                document.querySelectorAll('.form-check-input').forEach(checkbox => {
                    const risikoValue = checkbox.value;
                    const targetEmber = ember[risikoValue];
                    if (targetEmber) {
                        if (checkbox.checked) {
                            if (!map.hasLayer(targetEmber)) map.addLayer(targetEmber);
                        } else {
                            if (map.hasLayer(targetEmber)) map.removeLayer(targetEmber);
                        }
                    }
                });
            }

            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', updatePeta);
            });

            const filterControl = document.getElementById('filter-control');
            const filterToggle = document.getElementById('filter-toggle');
            filterToggle.addEventListener('click', function () {
                filterControl.classList.toggle('expanded');
            });
        });
    </script>
@endpush
