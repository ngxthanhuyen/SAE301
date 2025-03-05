document.addEventListener('DOMContentLoaded', () => {
    const favoriteButtons = document.querySelectorAll('.favorite-button');
    const modal = document.getElementById('confirmModal');
    const confirmYes = document.getElementById('confirmYes');
    const confirmNo = document.getElementById('confirmNo');
    let currentForm = null;
    const publicationCheckbox = document.getElementById('publication');
    const publicationStatus = document.getElementById('publicationStatus');
    const messageContainer = document.createElement('div'); 

    // Gestion des clics sur les boutons favoris
    favoriteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            currentForm = button.closest('form');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            }
        });
    });

    if (confirmYes) {
        confirmYes.addEventListener('click', () => {
            if (currentForm) {
                currentForm.submit();
            }
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        });
    }

    if (confirmNo) {
        confirmNo.addEventListener('click', () => {
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        });
    }

    // Fermer le modal si on clique en dehors
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    });

    // Gestion des clics sur les boutons de paramètres
    const parameterButtons = document.querySelectorAll('.parameter-btn');
    
    // Ajouter 'active' au premier bouton par défaut
    if (parameterButtons.length > 0) {
        parameterButtons[0].classList.add('active');
    }

    parameterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Enlever la classe active de tous les boutons
            parameterButtons.forEach(btn => btn.classList.remove('active'));

            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            const parameter = this.getAttribute('data-param');
            const stationSection = this.closest('.station-section');
            if (!stationSection) {
                console.error("Section station introuvable.");
                return;
            }
            const numStation = stationSection.querySelector('.favorite-button').getAttribute('data-num_station');
            if (!numStation) {
                console.error("Numéro de station introuvable.");
                return;
            }
            const dateInput = stationSection.querySelector('input[type="date"]');
            const selectedDate = dateInput ? dateInput.value : null;
            fetchDataAndDisplayChart(parameter, numStation, selectedDate);
        });
    });
    // Affichage du graphique de température par défaut pour chaque station
    const stations = document.querySelectorAll('.station-section');
    stations.forEach(station => {
        const numStation = station.querySelector('.favorite-button').getAttribute('data-num_station');
        if (numStation) {
            const dateInput = station.querySelector('input[type="date"]');
            const selectedDate = dateInput ? dateInput.value : null;
            // Paramètre par défaut = température
            fetchDataAndDisplayChart('temperature', numStation, selectedDate);
        }
    });
    const closeButtons = document.querySelectorAll(".close-button");
    const tableConfirmNoButtons = document.querySelectorAll(".tableConfirmNo");
    const tableConfirmYesButtons = document.querySelectorAll(".tableConfirmYes");

    closeButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            //On récupère l'index du bouton fermé
            const index = e.target.getAttribute("data-index");
            // On sélectionne le modal correspondant à l'index
            const modal = document.getElementById("confirmTableModal-" + index);
            modal.classList.remove("hidden");
            modal.style.display = "block";
        });
    });

    tableConfirmNoButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            // On récupère l'index pour fermer le bon modal
            const index = e.target.getAttribute("data-index");
            const modal = document.getElementById("confirmTableModal-" + index);
            modal.classList.add("hidden");
            modal.style.display = "none";
        });
    });

    // Écouteur d'événement pour les boutons "Oui" dans le modal
    tableConfirmYesButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            // Récupération de l'index du formulaire
            const index = e.target.getAttribute("data-index");
            // Sélectionner le formulaire associé
            const form = document.getElementById("confirmTableForm-" + index);
            if (form) {
                form.submit();
            }
            // Fermer le modal après la soumission
            const modal = document.getElementById("confirmTableModal-" + index);
            modal.classList.add("hidden");
            modal.style.display = "none";
        });
    });

    window.addEventListener("click", (event) => {
        if (event.target && event.target.classList.contains('modal')) {
            const modal = event.target;
            modal.classList.add("hidden");
            modal.style.display = "none";
        }
    });
    if (publicationCheckbox && publicationStatus) {
        publicationCheckbox.addEventListener('change', function() {
            // Récupérer l'état de la case et mettre à jour la valeur de l'input caché
            publicationStatus.value = this.checked ? '1' : '0';
            console.log('publicationStatus:', publicationStatus.value);  
        });
    }
    // Initialisation pour s'assurer que la valeur correspond bien au statut de la case à cocher lors du chargement
    if (!publicationCheckbox.checked) {
        // Si on décoche au chargement, la valeur sera 0
        publicationStatus.value = '0'; 
    }
    // Sélectionner le formulaire de publication
    const publicationForm = document.getElementById('publicationForm');
       // Empêcher la soumission du formulaire par défaut et envoyer la requête fetch
    if (publicationForm) {
        publicationForm.addEventListener('submit', function(e) {
            e.preventDefault();  

            const formData = new FormData();
            formData.append('publication', publicationStatus.value);
            fetch('meteotheque.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) 
            .then(data => {
                // Afficher le message de succès
                if (data.success) {
                    publicationCheckbox.checked = publicationStatus.value === '1';
                    messageContainer.innerHTML = data.message;
                    messageContainer.className = `flash ${data.type}`;
                    document.body.appendChild(messageContainer); 
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        });
    } 
    const datePickers = document.querySelectorAll('input[type="date"]');
    datePickers.forEach(datePicker => {
        //On récupère la date enregistrée au chargement de la page
        const stationSection = datePicker.closest('.station-section');
        if (!stationSection) {
            console.error("Section station introuvable.");
            return;
        }
        const numStation = stationSection.querySelector('.favorite-button').getAttribute('data-num_station');
        if (!numStation) {
            console.error("Numéro de station introuvable.");
            return;
        }

        const savedDate = localStorage.getItem(`selectedDate_${numStation}`);
        if (savedDate) {
            datePicker.value = savedDate; 
        }

        datePicker.addEventListener('change', function() {
            const selectedDate = this.value;
            localStorage.setItem(`selectedDate_${numStation}`, selectedDate); 
        });
    });
});
function fetchDataAndDisplayChart(parameter, numStation, date = null) {
    let url = `meteotheque.php?parameter=${parameter}&num_station=${numStation}`;
    if (date) {
        url += `&date=${date}`;
    }

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau : ' + response.statusText);
            }
            return response.json();  
        })
        .then(data => {
            console.log('Données reçues :', data);  
            // On filtre les données pour les heures multiples de 3
            const filteredData = filterDataForHours(data);  
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
    //On filtre les heures multiples de 3
    Object.keys(data).forEach(hour => {
        const hourInt = parseInt(hour.split(':')[0], 10); // Extraire l'heure (partie avant les ":")

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
                pointHoverRadius: 5, 
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
                            label += context.raw + getUnitForParameter(parameter);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    display: true, 
                    title: {
                        display: true,
                        text: getUnitForParameter(parameter), 
                        color: '#12769E',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        color: '#787878',
                        font: {
                            size: 12,
                            weight: 'bold',
                        },
                    },
                    grid: {
                        display: false, 
                    }
                },
                x: {
                    display: true, 
                    title: {
                        display: true,
                        text: 'Heures', 
                        color: '#12769E',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        color: '#787878',
                        font: {
                            size: 12,
                            weight: 'bold',
                        },
                    },
                    grid: {
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


function getUnitForParameter(parameter) {
    switch(parameter) {
        case 'temperature': return '°C';
        case 'pression': return 'Pa';
        case 'vent': return 'm/s';
        case 'humidite': return '%';
        case 'visibilite': return 'm';
        case 'precipitation': return 'mm';
        default: return '';
    }
}


