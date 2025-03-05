document.addEventListener('DOMContentLoaded', () => {
    const selectInput = document.getElementById('data-type-display');
    const optionsList = document.getElementById('data-type-list');
    const hiddenInput = document.getElementById('data-type-hidden');
    const options = document.querySelectorAll('.options');

    let selectedDataType = 'variation';  

    selectInput.addEventListener('click', () => {
        optionsList.classList.toggle('show');
    });

    options.forEach(option => {
        option.addEventListener('click', (e) => {
            const value = e.target.getAttribute('data-value');
            const text = e.target.textContent;
            selectInput.textContent = text;
            hiddenInput.value = value;
            selectedDataType = value;  
            optionsList.classList.remove('show');
        });
    });
    // Définition des unités pour chaque type de donnée
    const units = {
        tc: '°C', 
        pres: 'Pa', 
        u: '%', 
        ff: 'm/s', 
        vv: 'm', 
        rr1: 'mm' 
    };

    // Initialisation de la carte
    const map = L.map('map').setView([46.603354, 1.888334], 6);  // Centrer sur la France
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let currentLayer = null;
    let currentZonageLayer = null;

    // Fonction de mise à jour du zonage (département ou région)
    function updateZonage() {
        const zonageFile = 'https://france-geojson.gregoiredavid.fr/repo/regions.geojson';

        fetch(zonageFile)
            .then(response => response.json())
            .then(data => {
                if (currentZonageLayer) map.removeLayer(currentZonageLayer);
                currentZonageLayer = L.geoJSON(data, {
                    style: function(feature) {
                        return {
                            fillColor: 'transparent',
                            weight: 1,
                            opacity: 1,
                            color: 'black',
                            fillOpacity: 0.7
                        };
                    }
                }).addTo(map);
            })
            .catch(err => console.error('Erreur de chargement du zonage:', err));
    }

    //Afficher le préchargeur et la superposition lorsque le formulaire est soumis
    function showPreloader() {
        document.getElementById('preloader').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    //Masquer le préchargeur et la superposition lorsque la page a fini de se charger
    function hidePreloader() {
        document.getElementById('preloader').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
    // Fonction pour regrouper les données de variation par zone (région ou département)
    function groupByZonage(geoJSON, zonageData) {
        const groupedData = [];
    
        // Itérer sur chaque zone de zonage (région ou département)
        zonageData.features.forEach(zone => {
            const zoneId = zone.properties.code;
            const zoneName = zone.properties.nom;
            let totalVariation = 0;
            let stationCount = 0; 
    
            // On itère sur chaque station et vérifie si elle appartient à la zone
            geoJSON.features.forEach(station => {
                if (isInZone(station.geometry.coordinates, zone.geometry)) {
                    const stationVariation = station.properties.variation;
    
                    // Vérifier si la variation est valide avant de l'ajouter
                    const parsedVariation = parseFloat(stationVariation);
                    if (!isNaN(parsedVariation)) {
                        totalVariation += parsedVariation;
                        stationCount++; 
                    }
                }
            });
    
            // Si des stations sont trouvées, calculer la moyenne des variations pour la zone
            if (stationCount > 0) {
                const averageVariation = (totalVariation / stationCount).toFixed(2); 
                groupedData.push({
                    type: 'Feature',
                    geometry: zone.geometry,
                    properties: {
                        zone: zoneName,
                        averageVariation: averageVariation, // Variation moyenne arrondie
                        stationsCount: stationCount // Ajouter le nombre de stations
                    }
                });
            }
        });
    
        return groupedData;
    }
    
    // Fonction pour vérifier si une station est dans une zone géographique (région/département)
    function isInZone(stationCoords, zoneGeometry) {
        const lat = stationCoords[1];
        const lon = stationCoords[0];

        //On utilise un algorithme géospatial pour vérifier si le point est dans la zone 
        return turf.booleanPointInPolygon([lon, lat], zoneGeometry);
    }

    // Fonction pour récupérer la couleur en fonction des variations
    function getColor(variation, type) {
        console.log("Type:", type, "Variation:", variation);

        const value = parseFloat(variation);

        if (type === 'tc') {
            // Par défaut pour les autres paramètres
            return value > 20 ? '#e53935' : 
            value > 15 ? '#ef5350' : 
            value > 10 ? '#e57373' : 
            value > 5  ? '#f88c8c' : 
            value >= 0  ? '#ffbda2' : 
            value > -5 ? '#81d4fa' : 
            value > -10 ? '#4fc3f7' : 
            value > -15 ? '#039be5' : 
            value > -20 ? '#0277bd' : 
            '#004b8d';
            }
            if (type === 'pres') { // Pression
                return value < -2000 ? '#002b5c' :    // Bleu foncé
                       value < -1000 ? '#004b8d' :    // Bleu moyen
                       value < -500  ? '#0277bd' :    // Bleu clair
                       value < -200  ? '#039be5' :    // Bleu pâle
                       value < -50   ? '#4fc3f7' :  
                       value <= 0 ? '#81d4fa' :
                       value < 50   ? '#ffbda2' :    // Neutre
                       value < 200   ? '#f88c8c' :    // Rose pâle
                       value < 500   ? '#e57373' :    // Rouge clair
                       value < 1000  ? '#ef5350' :    // Rouge moyen
                       value < 2000  ? '#e53935' :    // Rouge vif
                       '#de2d26';                    // Rouge foncé
            }
            

        
            if (type === 'u') { // Humidité
                return value < -50 ? '#08306b' :      // Bleu foncé (Humidité extrêmement faible)
                       value < -20 ? '#2171b5' :      // Bleu moyen (Humidité très faible)
                       value < -10 ? '#6baed6' :      // Bleu clair (Humidité faible)
                       value < -5  ? '#abcbea' :      // Bleu pâle (Humidité légèrement faible)
                       value <= 0   ? '#eff3ff' :      // Bleu très pâle (Variation proche de 0, négatif)
                       value < 5   ? '#ffbda2' :      // Rose pâle (Variation proche de 0, positif)
                       value < 10  ? '#fb6a4a' :      // Orange clair (Humidité modérée)
                       value < 20  ? '#de2d26' :      // Rouge moyen (Humidité élevée)
                       value < 50  ? '#a50f15' :      // Rouge vif (Humidité très élevée)
                       '#7a0177';                     // Violet foncé (Humidité extrêmement élevée)
            }
        
        if (type === 'ff') { // Vitesse du vent
            return value < -20 ? '#08306b' :       // Bleu moyen (Vent très fort négatif)
                   value < -10 ? '#2171b5' :       // Bleu clair (Vent modéré négatif)
                   value < -5  ? '#6baed6' :       // Bleu pâle (Vent faible négatif)
                   value <= 0   ? '#ffbda2' :       // Bleu très pâle (Variation proche de 0, négatif)
                   value < 5   ? '#facab3' :       // Rose très pâle (Variation proche de 0, positif)
                   value < 10  ? '#fb6a4a' :       // Orange clair (Vent faible positif)
                   value < 20  ? '#de2d26' :       // Rouge clair (Vent modéré positif)
                   value < 30  ? '#a50f15' :       // Rouge vif (Vent élevé positif)
                   '#a50f15';                     // Rouge foncé (Vent extrêmement fort positif)
        }        
        
        
        if (type === 'vv') { // Visibilité
            return value < -20000 ? '#002b5c' :      // Bleu nuit (Visibilité extrêmement basse, négatif)
                   value < -10000 ? '#004b8d' :      // Bleu marine (Visibilité très basse, négatif)
                   value < -5000 ? '#0277bd' :       // Bleu océan (Visibilité basse, négatif)
                   value < -2000 ? '#039be5' :       // Bleu ciel foncé (Visibilité modérée, négatif)
                   value < -500 ? '#4fc3f7' :        // Bleu clair (Visibilité réduite, proche de 0)
                   value <= 0 ? '#81d4fa' :           // Bleu pastel clair (Visibilité légèrement négative)
                   value < 500 ? '#ffbda2' :         // Rouge pâle (Visibilité légèrement positive)
                   value < 2000 ? '#f88c8c' :        // Rouge plus intense (Visibilité modérée)
                   value < 5000 ? '#e57373' :        // Rouge moyen (Visibilité moyenne)
                   value < 10000 ? '#ef5350' :       // Rouge vif (Visibilité bonne)
                   value < 20000 ? '#e53935' :       // Rouge intense (Visibilité très bonne)
                   '#b71c1c';                        // Rouge sombre (Visibilité exceptionnelle)
        }
        
        
        if (type === 'rr1') { // Précipitations
            return value > 10 ? '#a50f15' :      // Rouge foncé (>+20mm)
                   value > 5 ? '#de2d26' :      // Rouge clair (>+10mm)
                   value > 1  ? '#fb6a4a' :      // Rose pâle (>+5mm)
                   value > 0  ? '#facab3' :  
                   value <= 0 ? '#abcbea' :    // Très pâle rose (>+1mm)
                   value < -1  ? '#6baed6' :      // Bleu moyen (<-1mm)
                   value < -5  ? '#2171b5' :      // Bleu foncé (<-5mm)
                   value < -10 ? '#08306b' :      // Bleu marine (<-10mm)
                   '#08306b';                     // Bleu très foncé (<-20mm)
        }
        
    }    
    
    document.getElementById("data-type-list").addEventListener("click", function(e) {
        var type = e.target.getAttribute("data-value");
        document.getElementById("data-type-display").textContent = e.target.textContent;
        document.getElementById("data-type-hidden").value = type;
    
        var legendHtml = "";
        if (type === 'tc') {
            legendHtml = "<div style='background-color:#e53935;'>>20°C</div>" + 
                         "<div style='background-color:#ef5350;'>>15°C</div>" +
                         "<div style='background-color:#e57373;'>>10°C</div>" +
                         "<div style='background-color:#f88c8c;'>>5°C</div>" +
                         "<div style='background-color:#ffbda2;'>>0°C</div>" +
                         "<div style='background-color:#81d4fa;'><0°C</div>" +
                         "<div style='background-color:#4fc3f7;'><-5°C</div>" +
                         "<div style='background-color:#039be5;'><-10°C</div>" +
                         "<div style='background-color:#0277bd;'><-15°C</div>" +
                         "<div style='background-color:#004b8d;'><-20°C</div>";

        } else if (type === 'pres') {
            legendHtml = "<div style='background-color:#002b5c;'>&lt;-2000hPa</div>" +
             "<div style='background-color:#004b8d;'>&lt;-1000Pa</div>" +
             "<div style='background-color:#0277bd;'>&lt;-500Pa</div>" +
             "<div style='background-color:#039be5;'>&lt;-200Pa</div>" +
             "<div style='background-color:#4fc3f7;'>&lt;-50Pa</div>" +
             "<div style='background-color:#81d4fa;'>&lt;0Pa</div>" +
             "<div style='background-color:#ffbda2;'>&gt;0Pa</div>" +
             "<div style='background-color:#f88c8c;'>&gt;50Pa</div>" +
             "<div style='background-color:#fc9272;'>&gt;200Pa</div>" +
             "<div style='background-color:#e57373;'>&gt;500Pa</div>" +
             "<div style='background-color:#ef5350;'>&gt;1000Pa</div>" +
             "<div style='background-color:#de2d26;'>&gt;2000Pa</div>";

        } else if (type === 'u') {
            legendHtml = "<div style='background-color:#7a0177;'>&gt;50%</div>" +
                 "<div style='background-color:#a50f15;'>&gt;20%</div>" +
                 "<div style='background-color:#de2d26;'>&gt;10%</div>" +
                 "<div style='background-color:#fb6a4a;'>&gt;5%</div>" +
                 "<div style='background-color:#ffbda2;'>&gt;0%</div>" +
                 "<div style='background-color:#c6dbef;'>&lt;0%</div>" +
                 "<div style='background-color:#abcbea;'>&lt;-5%</div>" +
                 "<div style='background-color:#6baed6;'>&lt;-10%</div>" +
                 "<div style='background-color:#2171b5;'>&lt;-20%</div>" +
                 "<div style='background-color:#08306b;'>&lt;-50%</div>";
        } else if (type === 'ff') { // Vitesse du vent
            legendHtml = "<div style='background-color:#a50f15;'>&gt;20m/s</div>" +
                         "<div style='background-color:#de2d26;'>&gt;10m/s</div>" +
                         "<div style='background-color:#fb6a4a;'>&gt;5m/s</div>" +
                         "<div style='background-color:#facab3;'>&gt;0m/s</div>" +
                         "<div style='background-color:#abcbea;'>&lt;0m/s</div>" +
                         "<div style='background-color:#6baed6;'>&lt;-5m/s</div>" +
                         "<div style='background-color:#2171b5;'>&lt;-10m/s</div>" +
                         "<div style='background-color:#08306b;'>&lt;-20m/s</div>";
        } else if (type === 'vv') { // Visibilité
            legendHtml = "<div style='background-color:#002b5c;'>&lt;-20000m</div>" +
                 "<div style='background-color:#004b8d;'>&lt;-10000m</div>" +
                 "<div style='background-color:#0277bd;'>&lt;-5000m</div>" +
                 "<div style='background-color:#039be5;'>&lt;-2000m</div>" +
                 "<div style='background-color:#4fc3f7;'>&lt;-500m</div>" +
                 "<div style='background-color:#81d4fa;'>&lt;0m</div>" +
                 "<div style='background-color:#ffbda2;'>&gt;0m</div>" +
                 "<div style='background-color:#f88c8c;'>&gt;500m</div>" +
                 "<div style='background-color:#e57373;'>&gt;2000m</div>" +
                 "<div style='background-color:#ef5350;'>&gt;5000m</div>" +
                 "<div style='background-color:#e53935;'>&gt;10000m</div>" +
                 "<div style='background-color:#b71c1c;'>&gt;20000m</div>";
        } else if (type === 'rr1') { // Précipitations
            legendHtml = legendHtml = "<div style='background-color:#a50f15;'>>10mm</div>" + // Rouge foncé
            "<div style='background-color:#de2d26;'>>5mm</div>" + // Rouge clair
            "<div style='background-color:#fb6a4a;'>>1mm</div>" + // Rose pâle
            "<div style='background-color:#facab3;'>>0mm</div>" + // Très pâle rose
            "<div style='background-color:#abcbea;'><=0mm</div>" + // Bleu clair
            "<div style='background-color:#6baed6;'><-1mm</div>" + // Bleu moyen
            "<div style='background-color:#2171b5;'><-5mm</div>" + // Bleu plus foncé
            "<div style='background-color:#08306b;'><-10mm</div>"; // Bleu marine            

        }
    
        document.getElementById("color-legend").innerHTML = legendHtml;
        document.getElementById("color-legend-container").style.display = "block";
    });
    
    
    // Déclaration globale de currentMarkers pour stocker les marqueurs de stations
    let currentMarkers = []; 
    // Définir l'icône personnalisée
    const iconPersonnalise = L.icon({
        iconUrl: 'http://localhost/SAE3.01/static/images/marqueur.png',
        iconSize: [35, 35],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32],
    });

    function showStationsInRegion(regionGeometry) {
        // Supprimer les marqueurs existants
        currentMarkers.forEach(marker => map.removeLayer(marker));
        currentMarkers = [];

        // Parcourir les stations dans geoJSON.features
        geoJSON.features.forEach(feature => {
            const stationLat = feature.geometry.coordinates[1]; 
            const stationLon = feature.geometry.coordinates[0]; 

            // Accéder aux propriétés de la station
            const stationProperties = feature.properties;
            const station = stationProperties.station || "Station inconnue";
            const stationNom = stationProperties.nom_station || "Nom non spécifié"; 
            const moyenne_debut = stationProperties.moyenne_debut || "N/A";
            const moyenne_fin = stationProperties.moyenne_fin || "N/A";
            const stationVariation = parseFloat(stationProperties.variation); 

            // Vérifier si la station est dans la région
            if (turf.booleanPointInPolygon([stationLon, stationLat], regionGeometry)) {
                const marker = L.marker([stationLat, stationLon], { icon: iconPersonnalise }).addTo(map); // Application de l'icône personnalisée
                currentMarkers.push(marker);

                // Préparer l'affichage de la variation
                const variationDisplay = !isNaN(stationVariation)
                    ? `${stationVariation > 0 ? '+' : ''}${stationVariation.toFixed(2)}`
                    : "Variation inconnue";

                // Ajouter un popup avec les informations de la station
                marker.bindPopup(`
                    <strong>${stationNom} - ${station}</strong><br>
                    Moyenne début : ${moyenne_debut}<br>
                    Moyenne fin : ${moyenne_fin}<br>
                    Variation : ${variationDisplay}
                `).openPopup();
            }
        });
    }  

    // Fonction pour mettre à jour la carte 
    function updateMap(geoJSONData) {
        // On supprime la couche précédente si elle existe
        if (currentLayer) map.removeLayer(currentLayer);

        // Ajouter une nouvelle couche avec les zones colorées en fonction de la variation
        currentLayer = L.geoJSON(geoJSONData, {
            style: function (feature) {
                return {
                    fillColor: getColor(feature.properties.averageVariation, selectedDataType),
                    weight: 1,
                    opacity: 1,
                    color: 'black',
                    fillOpacity: 0.7
                };
            },
        
            onEachFeature: function (feature, layer) {
                const avgVariation = feature.properties.averageVariation;
                const stationsCount = feature.properties.stationsCount;
                // Récupère l'unité, vide si non défini
                const unit = units[selectedDataType] || ''; 
    
                // Ajouter un tooltip avec la variation et l'unité
                layer.bindTooltip(`${avgVariation > 0 ? '+' : ''}${avgVariation} ${unit}`, {
                    permanent: true,
                    direction: 'center',
                    className: 'zone-tooltip'
                }).openTooltip();

                // Ajouter un événement de clic sur la zone
                layer.on('click', function() {
                // Récupérer le centre de la zone (région)
                const center = layer.getBounds().getCenter(); 

                //Zoomer et centrer sur la région de façon fluide
                map.flyTo(center, 7.5, { 
                duration: 0.4  
                });

                // Afficher les marqueurs des stations dans cette région
                showStationsInRegion(feature.geometry);
            });

            }
        }).addTo(map);
    }

    let geoJSON = null; // Déclaration globale de geoJSON

    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
    
        showPreloader();
    
        const formData = new FormData(this);
    
        fetch('climatique.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hidePreloader();
            console.log('Données reçues :', data);
    
            if (!data.features || data.features.length === 0) {
                console.log('Aucune donnée trouvée');
            } else {
                geoJSON = data; 
    
                // Récupérer le zonage et regrouper par zone
                fetchZonageData()
                    .then(zonageData => {
                        const groupedData = groupByZonage(geoJSON, zonageData);
                        updateMap({
                            type: 'FeatureCollection',
                            features: groupedData
                        });
                    });
            }
        });
    });
   
    // Fonction pour récupérer les données de zonage
    function fetchZonageData() {
        const zonageFile = 'https://france-geojson.gregoiredavid.fr/repo/regions.geojson';

        return fetch(zonageFile)
            .then(response => response.json());
    }
    updateZonage();
});
