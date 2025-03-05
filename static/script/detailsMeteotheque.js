document.addEventListener('DOMContentLoaded', () => {
    const stations = document.querySelectorAll('.station-section');
    if (stations.length === 0) {
        console.error("Aucune station trouvée dans le DOM.");
        return;
    }

    // Pour chaque station, récupérer le numéro de station et initialiser les graphiques pour chaque paramètre
    stations.forEach(station => {
        const numStation = station.getAttribute('data-num_station');
        if (numStation) {
            // Paramètre par défaut = température
            fetchDataAndDisplayChart('temperature', numStation);
        } else {
            console.error("Numéro de station introuvable pour cette station.");
        }
    });

    // Ajout de l'écouteur d'événement pour les boutons de paramètres
    const parameterButtons = document.querySelectorAll('.parameter-btn');
    parameterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const parameter = this.getAttribute('data-param');
            const numStation = this.closest('.station-section').getAttribute('data-num_station');
            fetchDataAndDisplayChart(parameter, numStation);
        });
    });
});

// Fonction pour récupérer les données et afficher le graphique
function fetchDataAndDisplayChart(parameter, numStation) {
    const url = `detailsMeteotheque.php?parameter=${parameter}&num_station=${numStation}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau : ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('Données reçues :', data);

            // Vérifiez si les données sont vides
            if (Object.keys(data).length === 0) {
                console.error('Aucune donnée disponible pour ce paramètre.');
                alert('Aucune donnée disponible pour ce paramètre.');
                return;
            }

            // On filtre les données pour les heures multiples de 3
            const filteredData = filterDataForHours(data);
            if (Object.keys(filteredData).length === 0) {
                console.error('Aucune donnée filtrée pour des heures multiples de 3.');
                alert('Aucune donnée filtrée pour des heures multiples de 3.');
                return;
            }

            displayChart(filteredData, parameter, numStation);
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des données:', error);
            alert('Erreur lors de la récupération des données. Veuillez réessayer plus tard.');
        });
}

// Fonction pour filtrer les données pour ne garder que les heures multiples de 3 et les trier dans l'ordre croissant
function filterDataForHours(data) {
    const filteredData = {};

    // Filtrer les heures multiples de 3
    Object.keys(data).forEach(hour => {
        const hourInt = parseInt(hour.split(':')[0], 10); 

        if (hourInt % 3 === 0) {
            filteredData[hour] = data[hour];
        }
    });

    // Trier les heures dans l'ordre croissant
    const sortedHours = Object.keys(filteredData).sort((a, b) => {
        const hourA = parseInt(a.split(':')[0], 10);
        const hourB = parseInt(b.split(':')[0], 10);
        return hourA - hourB;
    });

    // Recomposer les données triées
    const sortedData = {};
    sortedHours.forEach(hour => {
        sortedData[hour] = filteredData[hour];
    });

    return sortedData;
}

// Fonction pour afficher le graphique
function displayChart(data, parameter, numStation) {
    const chartContainer = document.getElementById(`chart-container-${numStation}`);
    const canvas = chartContainer.querySelector(`#dynamic-chart-${numStation}`);

    if (!canvas || !chartContainer) {
        console.error("Conteneur ou canvas introuvable.");
        return;
    }

    const ctx = canvas.getContext('2d');

    // Détruire tout graphique existant pour éviter des conflits
    if (window.chart && window.chart[numStation]) {
        window.chart[numStation].destroy();
    }

    if (!window.chart) window.chart = {};

    // Création du graphique
    window.chart[numStation] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(data),
            datasets: [{
                data: Object.values(data),
                borderColor: '#12769E',
                backgroundColor: 'rgba(18, 118, 158, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 0,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw + getUnitForParameter(parameter); // Afficher la valeur avec l'unité
                            return label;
                        }
                    }
                },
                annotation: {
                    annotations: Object.entries(data).map(([key, value], index) => ({
                        type: 'label',
                        xValue: key,
                        yValue: value,
                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        borderRadius: 4,
                        color: '#12769E',
                        font: {
                            size: 12,
                            weight: 'bold',
                        }
                    })),
                },
            },
            scales: {
                y: {
                    display: false,
                },
                x: {
                    ticks: {
                        color: '#787878',
                        font: {
                            size: 12,
                            weight: 'bold',
                        },
                    },
                    padding: 0,
                    grid: {
                        display: false,
                    },
                    border: {
                        display: false,
                    }
                }
            }
        }
    });

    // Appliquer un style au conteneur pour le fond bleu
    chartContainer.style.borderRadius = '10px';
    chartContainer.style.padding = '15px';
    chartContainer.style.display = 'block';
}

// Fonction pour obtenir l'unité du paramètre
function getUnitForParameter(parameter) {
    switch(parameter) {
        case 'temperature': return '°C';
        case 'pression': return 'hPa';
        case 'vent': return 'm/s';
        case 'humidite': return '%';
        case 'visibilite': return 'm';
        case 'precipitation': return 'mm';
        default: return '';
    }
}
