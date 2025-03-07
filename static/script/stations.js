// Initialisation des cartes
const mapMetropole = L.map('map-metropole').setView([46.603354, 1.888334], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapMetropole);

const mapOutreMer = L.map('map-outre-mer').setView([-21.135, 55.5364], 3);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapOutreMer);

// Liste des régions métropolitaines
const regionsMetropolitaines = [
    'Auvergne-Rhône-Alpes', 'Bourgogne-Franche-Comté', 'Bretagne', 
    'Centre-Val de Loire', 'Corse', 'Grand Est', 'Hauts-de-France', 
    'Île-de-France', 'Normandie', 'Nouvelle-Aquitaine', 'Occitanie', 
    'Pays de la Loire', 'Provence-Alpes-Côte d\'Azur'
];

// Définir les coordonnées des zones d'Outre-Mer
const zonesOutreMer = {
    atlantique: [14.6415, -61.0242], // Martinique
    indien: [-20.8789, 55.4481], // La Réunion
};

// Variables pour stocker les marqueurs
const markersOutreMer = [];
const markersMetropole = [];

// Ajouter les marqueurs à la carte Métropole et Outre-Mer
stations.forEach(station => {
    const { latitude, longitude, nom, libgeo, nom_reg, num_station, zone } = station;

    if (latitude && longitude) {
        // Définir l'icône personnalisée
        const iconPersonnalise = L.icon({
            iconUrl: '../../static/images/marqueur_station.png',
            iconSize: [35, 35],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        });

        const iconPersonnaliseOutreMer = L.icon({
            iconUrl: '../../static/images/marqueur.png',
            iconSize: [35, 35],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        });

        // Ajouter marqueurs pour la métropole
        if (regionsMetropolitaines.includes(nom_reg)) {
            const marker = L.marker([latitude, longitude], { icon: iconPersonnalise }).addTo(mapMetropole);
            marker.on('click', function () {
                window.location.href = "StationsInfos.php?num_station=" + num_station;
            });
            markersMetropole.push(marker); // Stocker le marqueur
        } 
        // Ajouter marqueurs pour l'Outre-Mer
        else {
            const marker = L.marker([latitude, longitude], { icon: iconPersonnaliseOutreMer }).addTo(mapOutreMer);
            marker.on('click', function () {
                window.location.href = "StationsInfos.php?num_station=" + num_station;
            });
            markersOutreMer.push({ marker, zone }); 
        }
    } else {
        console.log(`Coordonnées manquantes pour la station: ${nom}`);
    }
});

// Gestion du déplacement de la carte d'Outre-Mer
document.addEventListener('DOMContentLoaded', function () {
    const oceanSelect = document.querySelector('.ocean-select');

    oceanSelect.addEventListener('change', function (e) {
        const selectedZone = e.target.value;

        if (zonesOutreMer[selectedZone]) {
            // Déplacer la carte vers la zone sélectionnée
            const [lat, lng] = zonesOutreMer[selectedZone];
            mapOutreMer.setView([lat, lng], 3); 
        }
    });
});

// Gestion du formulaire de recherche
document.addEventListener("DOMContentLoaded", function () {
    const searchForm = document.getElementById("searchForm");
    const searchButton = document.querySelector(".search-button");

    // Ajouter un événement de clic pour soumettre le formulaire
    searchButton.addEventListener("click", function () {
        searchForm.submit();
    });
});
