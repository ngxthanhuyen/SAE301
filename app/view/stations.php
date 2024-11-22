<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Météorologique avec Leaflet</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <h1>Carte Météorologique des Départements</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialisation de la carte
        var map = L.map('map').setView([46.6034, 1.8883], 5); // Centre de la France

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

       

        // Fonction pour chaque feature (département)
        function onEachFeature(feature, layer) {
            layer.on({
                click: function (e) {
                    // Appeler la fonction pour filtrer les villes
                    filterVilles(feature.properties.code); // Code du département
                }
            });
            layer.bindPopup(feature.properties.nom); // Afficher le nom du département
        }

        // Charger les données GeoJSON
        fetch('https://france-geojson.gregoiredavid.fr/repo/departements.geojson')
            .then(response => response.json())
            .then(data => {
                L.geoJson(data, {
                    style: style,
                    onEachFeature: onEachFeature
                }).addTo(map);
            });
        function style() {
            return {
                color: '#000000',
                weight: 1,
                opacity: 0.25,
                fillColor: '#86aae0',
                fillOpacity: 1
            };
        }

        function highlightFeature(e) {
            const layer = e.target;
            if (layer !== clickedLayer && !selectedDepartment) {
                layer.setStyle({
                weight: 5,
                color: '#666',
                fillColor: '#86cce0',
                fillOpacity: 0.7
                });
            }
            info.update(layer.feature.properties);
        }


        // Fonction pour filtrer et afficher les villes d'un département
        function filterVilles(departmentCode) {
            // Suppression des anciens marqueurs
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });

            var villesData = {
                '75': [
                    { nom: 'Paris', coords: [48.8566, 2.3522] },
                    { nom: '1er arrondissement', coords: [48.8638, 2.3368] }
                ],
                '92': [
                    { nom: 'Boulogne-Billancourt', coords: [48.8347, 2.2399] },
                    { nom: 'Nanterre', coords: [48.8954, 2.2056] }
                ],
            };

            // Vérifier si le département a des villes associées
            if (villesData[departmentCode]) {
                var villes = villesData[departmentCode];
                villes.forEach(function(ville) {
                    L.marker(ville.coords).addTo(map)
                        .bindPopup(ville.nom)
                        .openPopup();
                });

                // Centrer la carte sur le premier marqueur de la ville
                var coords = villes[0].coords;
                map.setView(coords, 12);
            }
        }
    </script>
</body>
</html>
