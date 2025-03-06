let chartInstances = {}; // Pour stocker les instances des graphiques

document.addEventListener('DOMContentLoaded', () => {
    //Restreindre les sélection de dates
    let today = new Date();
    today.setDate(today.getDate() - 1); 
    let maxDate = today.toISOString().split("T")[0]; 

    let dateJournee = document.getElementById("date-journee");
    if (dateJournee) {
        dateJournee.setAttribute("max", maxDate);
    }
    
    //Restreindre les sélection de semaines
    let lastWeek = new Date(today);
    lastWeek.setDate(lastWeek.getDate() - 7);

    // Récupérer l'année et la semaine précédente au format YYYY-Www
    let firstDayOfYear = new Date(lastWeek.getFullYear(), 0, 1);
    let pastDaysOfYear = (lastWeek - firstDayOfYear) / 86400000;
    let weekNumber = Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);

    let maxWeek = lastWeek.getFullYear() + "-W" + String(weekNumber).padStart(2, "0");

    let weekInput = document.getElementById("date-semaine");
    if (weekInput) {
        weekInput.setAttribute("max", maxWeek);

        // Empêcher la sélection d'une semaine future
        weekInput.addEventListener("input", function () {
            if (this.value > maxWeek) {
                this.value = maxWeek;
            }
        });
    }
    //Restreindre les sélection de mois
    let lastMonth = new Date(today.getFullYear(), today.getMonth() - 1);

    // Format YYYY-MM
    let maxMonth = lastMonth.getFullYear() + "-" + String(lastMonth.getMonth() + 1).padStart(2, "0");
 
    let monthInput = document.getElementById("monthInput");
    if (monthInput) {
        monthInput.setAttribute("max", maxMonth);
 
        // Empêcher la sélection d'un mois futur
        monthInput.addEventListener("input", function () {
            if (this.value > maxMonth) {
                this.value = maxMonth;
            }
        });
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
    const selectInputDept = document.getElementById('dept-select');
    const optionsListDept = document.getElementById('dept-list');
    const hiddenInputDept = document.getElementById('dept-hidden');

    const selectInputStation = document.getElementById('station-select');
    const optionsListStation = document.getElementById('station-list');
    const hiddenInputStation = document.getElementById('station-hidden');

    const regionSelect = document.getElementById("region-select");
    const regionList = document.getElementById("region-list");
    const regionHiddenInput = document.getElementById("region-hidden");

    
    let departments = []; // On garde ici les départements récupérés par PHP

    // Récupérer les départements via le PHP
    const deptItems = document.querySelectorAll('.options');
    deptItems.forEach(item => {
        departments.push({
            value: item.getAttribute('data-value'),
            text: item.textContent
        });
    });

    // Gérer la sélection d'un département
    selectInputDept.addEventListener('click', () => {
        optionsListDept.classList.toggle('show');
    });

    optionsListDept.addEventListener('click', (e) => {
        if (e.target.classList.contains('options')) {
            optionsListDept.querySelectorAll('.options').forEach(option => option.classList.remove('selected'));
            e.target.classList.add('selected');
            const selectedValue = e.target.getAttribute('data-value');
            const selectedText = e.target.textContent;
            selectInputDept.textContent = selectedText;
            hiddenInputDept.value = selectedValue;
            optionsListDept.classList.remove('show');
            
            // Mettre à jour la liste des stations en fonction du département sélectionné
            const deptCode = selectedValue.split('|')[0]; // Récupérer le code du département
        }
    });

    // Fermer la liste si on clique en dehors
    document.addEventListener('click', (e) => {
        if (!selectInputDept.contains(e.target) && !optionsListDept.contains(e.target)) {
            optionsListDept.classList.remove('show');
        }
        if (!selectInputStation.contains(e.target) && !optionsListStation.contains(e.target)) {
            optionsListStation.classList.remove('show');
        }
    });

    // Gérer la sélection d'une station
    selectInputStation.addEventListener('click', () => {
        optionsListStation.classList.toggle('show');
    });

    optionsListStation.addEventListener('click', (e) => {
        e.stopPropagation(); // Empêcher la propagation de l'événement
        if (e.target.classList.contains('options')) {
            optionsListStation.querySelectorAll('.options').forEach(option => option.classList.remove('selected'));
            e.target.classList.add('selected');
    
            const selectedValue = e.target.getAttribute('data-value');  // 'numerostation' | 'nomstation'
            const selectedText = e.target.textContent;
    
            selectInputStation.textContent = selectedText;
    
            // On garde uniquement la partie 'numerostation'
            const stationNumber = selectedValue.split('|')[0];  // Sépare par '|' et garde le premier élément
    
            hiddenInputStation.value = stationNumber;  // Utilise 'numerostation' uniquement
            optionsListStation.classList.remove('show');
        }
    });    
    
    // Fermer la liste si on clique en dehors du select ou de la liste des stations
    document.addEventListener('click', (e) => {
        if (!selectInputStation.contains(e.target) && !optionsListStation.contains(e.target)) {
            optionsListStation.classList.remove('show');
        }
    });
        
    
    regionSelect.addEventListener("click", function() {
        regionList.classList.toggle("show");
    });

    // Fonction pour gérer la sélection d'une option
    regionList.addEventListener("click", function(event) {
        const selectedOption = event.target;
        if (selectedOption.classList.contains("options")) {
            const selectedValue = selectedOption.getAttribute("data-value");
            
            regionSelect.textContent = selectedOption.textContent;
            
            regionHiddenInput.value = selectedValue;
    
            regionList.classList.remove("show");
        }
    });

    // Fermer la liste si l'utilisateur clique en dehors
    document.addEventListener("click", function(event) {
        if (!regionSelect.contains(event.target) && !regionList.contains(event.target)) {
            regionList.classList.remove("show");
        }
    });
    
    const anneeSelect = document.getElementById('annee-select');
    const anneeList = document.getElementById('annee-list');
    const anneeHidden = document.getElementById('annee-hidden');


    // Afficher la liste des années lorsque l'utilisateur clique
    anneeSelect.addEventListener('click', () => {
        anneeList.classList.toggle('show');
    });

    // Gérer la sélection d'une année
    anneeList.addEventListener('click', (e) => {
        if (e.target && e.target.tagName === 'LI') {
            anneeSelect.textContent = e.target.textContent;  // Afficher l'année sélectionnée
            anneeHidden.value = e.target.getAttribute('data-value');  // Enregistrer la valeur cachée
            anneeList.classList.remove('show');  // Masquer la liste après la sélection
        }
    });

    // Fermer les listes si l'utilisateur clique en dehors
    document.addEventListener('click', (e) => {
        if (!anneeSelect.contains(e.target) && !anneeList.contains(e.target)) {
            anneeList.classList.remove('show');
        }
    });

    document.getElementById('valider').addEventListener('click', function() {
        const stationSelection = document.getElementById('station-hidden').value;
        let dateSelection = document.getElementById('date-journee').value;
        let semaineSelection = document.getElementById('date-semaine').value;
        let moisSelection = document.getElementById('monthInput').value;
        let anneeSelection = document.getElementById('annee-hidden').value;

        clearExistingElements(); 
        // Vérifier si une station est sélectionnée et qu'une date, une semaine, un mois ou une année est choisi
        if (stationSelection && (dateSelection || semaineSelection || moisSelection || anneeSelection)) {
            let url = '';
            let dataType = ''; // Indiquer si on a une date, une semaine, un mois ou une année

            if (semaineSelection) {
                url = `dashboard.php?station=${stationSelection}&semaine_selection=${semaineSelection}`;
                dataType = 'semaine';  
            } else if (dateSelection) {
                url = `dashboard.php?station=${stationSelection}&date_selection=${dateSelection}`;
                dataType = 'date'; 
            } else if (moisSelection) {
                url = `dashboard.php?station=${stationSelection}&mois_selection=${moisSelection}`;
                dataType = 'mois';
            } else if (anneeSelection) {
                url = `dashboard.php?station=${stationSelection}&annee_selection=${anneeSelection}`;
                dataType = 'annee';
            }

            // Afficher l'URL dans la console avant d'envoyer la requête
            console.log('URL envoyée :', url);

            showPreloader();

            // Récupération des données via fetch
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP : ' + response.status);
                    }
                    return response.text(); // Get the raw response text
                })
                .then(text => {
                    try {
                        // Check if the response is valid JSON
                        const data = JSON.parse(text);
                        console.log('Données reçues :', data);
                        document.getElementById('gauge-container').innerHTML = '';
                        document.getElementById('graph-container').innerHTML = '';
                        document.getElementById('table-container').innerHTML = '';
                        document.getElementById('weather-widget').style.display = 'none';
    
                        if (data && Object.keys(data).length > 0) {  
                            if (dataType === 'date') {
                                // Afficher les éléments de la journée
                                displayWeatherWidget(data);
                                displayGaugesJournee(data.moyennes);
                                displayTable(data.mesures); 
                                displayGraphsJournee(data.mesures);
                            } else if (dataType === 'semaine') {
                                // Afficher la table des moyennes de la semaine
                                displayGaugesSemaine(data.moyennesSemaine);
                                afficherTableSemaine(data.mesuresSemaine);
                                displayTempGraphSemaine(data.mesuresSemaine);
                                displayPluvioGraphSemaine(data.mesuresSemaine);
                                displayGraphsSemaine(data.mesuresSemaine);
                            } else if (dataType === 'mois') {
                                // Afficher les éléments du mois
                                displayGaugesMois(data.moyennesMois);
                                displayTableMois(data.mesuresMois);
                                displayGraphsMois(data.mesuresMois);
                            } else if (dataType === 'annee') {
                                // Afficher les éléments de l'année
                                displayGaugesAnnee(data.moyennesAnnee);
                                displayTableAnnee(data.mesuresAnnee);
                                displayGraphsAnnee(data.mesuresAnnee);
                            }
                        } else {
                            console.error("Erreur : Aucune donnée à afficher ou structure de données incorrecte");
                        }
                    } catch (error) {
                        console.error("Erreur lors de l'analyse des données JSON :", error);
                        console.log("Réponse brute :", text); // Log the raw response text
                        alert("Erreur lors de la récupération des données. Veuillez réessayer plus tard.");
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération des données :", error);
                })
                .finally(() => {
                    hidePreloader();
                    // Effacer les champs de date, semaine, mois et année après la requête
                    document.getElementById('date-journee').value = '';
                    document.getElementById('date-semaine').value = '';
                    document.getElementById('monthInput').value = '';
                    document.getElementById('annee-hidden').value = '';
                });
            } else {
                alert("Veuillez sélectionner une station et une date, une semaine, un mois ou une année.");
            }
        });       
       
   
    function clearExistingElements() {
        document.getElementById('gauge-container').innerHTML = '';  
        document.getElementById('graph-container').innerHTML = '';  
        document.getElementById('table-container').innerHTML = '';  
        document.getElementById('weather-widget').style.display = 'none';  
        document.getElementById('graphContainer').style.display = 'none';  
        const graphContainer = document.getElementById('graph-container');
        if (graphContainer) {
            graphContainer.innerHTML = '';  // Effacer les graphiques
    
            // Supprimer le titre existant si présent
            const existingTitle = graphContainer.previousElementSibling;
            if (existingTitle && existingTitle.classList.contains('graph-title')) {
                existingTitle.remove();  // Supprimer immédiatement le titre
            }
        }
    }
        
    // Fonction pour afficher le widget de météo pour une station et une date sélectionnées
    function displayWeatherWidget(data) {
        const weatherWidget = document.getElementById('weather-widget');
        const station = document.getElementById('nom-station');
        const tempCurrent = document.getElementById('temp-current');
        const tempHigh = document.getElementById('temp-high');
        const tempLow = document.getElementById('temp-low');
        const dayName = document.getElementById('day-name');
        const fullDate = document.getElementById('full-date');
        const weatherIcon = document.getElementById('weather-icon');
        const statusText = document.getElementById('status-text'); 
        
        if (data && data.moyennes && data.mesures) {
            weatherWidget.style.display = 'block';
    
            // Remplir les informations
            station.textContent = `${data.nom_station}`;
            tempCurrent.textContent = `${data.moyennes.temperature}°C`; // Température actuelle
            tempHigh.textContent = `${Math.max(...data.mesures.map(m => m.temperature))}°C`; // Température maximale
            tempLow.textContent = `${Math.min(...data.mesures.map(m => m.temperature))}°C`; // Température minimale
            dayName.textContent = data.jour; 
            fullDate.textContent = data.date_selectionnee; 
            
            // Icône et description météo
            weatherIcon.textContent = data.weatherIcon; 
            statusText.textContent = data.weatherDescription; 
        } else {
            // Masquer le widget si les données sont manquantes
            weatherWidget.style.display = 'none';
        }
    }    
    function displayGaugesJournee(moyennes) {
        const gaugeContainer = document.getElementById('gauge-container');
        gaugeContainer.innerHTML = ''; // On vide le conteneur avant d'ajouter les nouvelles jauges
    
        if (moyennes && Object.keys(moyennes).length > 0) {
            // Ajouter le titre au-dessus des jauges
            const title = document.createElement('h1');
            title.classList.add('dashboard-title');
            title.textContent = 'Observations moyennes de la journée';
            gaugeContainer.appendChild(title);
            
            // Paramètres et unités
            const parameterLabels = {
                'temperature': 'Température',
                'vent': 'Vent',
                'humidite': 'Humidité',
                'pression': 'Pression'
            };
    
            const units = {
                'temperature': '°C',
                'vent': 'm/s',
                'humidite': '%',
                'pression': 'Pa'
            };
    
            const parametres = ['temperature', 'vent', 'humidite', 'pression'];
    
            Object.keys(moyennes).forEach(parameter => {
                if (parametres.includes(parameter)) {  
                    const value = moyennes[parameter];
                    const label = parameterLabels[parameter] || parameter;
                    const unit = units[parameter] || '';
    
                   // Création du conteneur principal pour la jauge
                   const gaugeDiv = document.createElement('div');
                   gaugeDiv.classList.add('gauge');
       
                   // Création du canvas pour le graphique
                   const canvas = document.createElement('canvas');
                   canvas.classList.add('gauge-canvas');
                   gaugeDiv.appendChild(canvas);
       
                   // Création du conteneur pour la valeur et l'unité
                   const valueContainer = document.createElement('div');
                   valueContainer.classList.add('gauge-value');
       
                   // Création des span pour la valeur
                   const valueSpan = document.createElement('span');
                   valueSpan.classList.add('value');
                   valueSpan.textContent = value;
       
                   // Création des span pour l'unité
                   const unitSpan = document.createElement('span');
                   unitSpan.classList.add('unit');
                   unitSpan.textContent = unit;
       
                   // Ajout de la valeur et de l'unité au conteneur
                   valueContainer.appendChild(valueSpan);
                   valueContainer.appendChild(unitSpan);
       
                   // Ajout du conteneur au gaugeDiv
                   gaugeDiv.appendChild(valueContainer);
       
                   // Création du label
                   const labelDiv = document.createElement('div');
                   labelDiv.classList.add('gauge-label');
                   labelDiv.textContent = label;
       
                   // Ajout du label au gaugeDiv
                   gaugeDiv.appendChild(labelDiv);
       
                   // Ajout du gaugeDiv au conteneur principal
                   gaugeContainer.appendChild(gaugeDiv);
       
                   // Initialisation du graphique
                   const ctx = canvas.getContext('2d');
        
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [value, 100 - value],
                                backgroundColor: ['#108439', '#E0E0E0'], 
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%', 
                            circumference: 270,
                            rotation: -135,
                            plugins: {
                                tooltip: { enabled: false },
                                legend: { display: false },
                            },
                            elements: {
                                arc: {
                                    borderRadius: 5, 
                                },
                            },
                        },
                    });
                }
            });
        }
    }
    
    
    function displayTable(mesures) {
        const tableContainer = document.getElementById('table-container');
        tableContainer.innerHTML = ''; // On vide le conteneur avant d'ajouter le nouveau tableau
    
        if (!mesures || mesures.length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher dans le tableau.');
            tableContainer.textContent = 'Aucune donnée disponible pour le tableau.';
            return;
        }
    
        // Trier les mesures par heure dans l'ordre croissant
        mesures.sort((a, b) => {
            const heureA = a.time.split(':').map(Number);
            const heureB = b.time.split(':').map(Number);
            const dateA = new Date();
            const dateB = new Date();
            dateA.setHours(heureA[0], heureA[1]);
            dateB.setHours(heureB[0], heureB[1]);
            return dateA - dateB;
        });
    
        // Création du tableau
        const table = document.createElement('table');
        table.classList.add('mesures-table');
    
        // Création de l'en-tête
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        const headers = ['Heure', 'Température (°C)', 'Vent (m/s)', 'Humidité (%)', 'Pression (Pa)', 'Visibilité(m)', 'Précipitation(mm)'];
        headers.forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);
    
        // Création du corps du tableau
        const tbody = document.createElement('tbody');
        mesures.forEach(mesure => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${mesure.time}</td>
                <td>${mesure.temperature}</td>
                <td>${mesure.vent}</td>
                <td>${mesure.humidite}</td>
                <td>${mesure.pression}</td>
                <td>${mesure.visibilite}</td>
                <td>${mesure.precipitation}</td>
            `;
            tbody.appendChild(row);
        });
        table.appendChild(tbody);
    
        tableContainer.appendChild(table);
    }
    
    function displayGraphsJournee(mesures) {
        const graphContainer = document.getElementById('graph-container');
    
        // Vérifier si le conteneur a été trouvé
        if (!graphContainer) {
            console.error('Erreur : Élément avec la classe "graph-container" non trouvé.');
            return;
        }
    
        graphContainer.innerHTML = ''; 
    
        // Vérifier si le tableau 'mesures' contient des données
        if (!mesures || mesures.length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher pour les graphiques.');
            graphContainer.textContent = 'Aucune donnée disponible pour les graphiques.';
            return;
        }
    
        // Définir les unités pour chaque paramètre
        const units = {
            'temperature': '°C',
            'vent': 'm/s',
            'humidite': '%',
            'pression': 'Pa'
        };
    
        // Supprimer le titre existant s'il y en a un
        const existingTitle = graphContainer.previousElementSibling;
        if (existingTitle && existingTitle.classList.contains('graph-title')) {
            existingTitle.remove();
        }

        // Créer le titre
        const graphTitle = document.createElement('h1');
        graphTitle.classList.add('graph-title');
        graphTitle.textContent = 'Graphiques des mesures avec intervalle de 3 heures';

        // Ajouter le titre au-dessus du conteneur
        graphContainer.parentNode.insertBefore(graphTitle, graphContainer);
    
        const parameters = ['temperature', 'vent', 'humidite', 'pression'];
    
        parameters.forEach(parameter => {
            // Trier les mesures par heure
            mesures.sort((a, b) => {
                const timeA = parseInt(a.time.split(':')[0]);
                const timeB = parseInt(b.time.split(':')[0]);
                return timeA - timeB;
            });
    
            const labels = mesures.map(entry => entry.time);
            const values = mesures.map(entry => entry[parameter]);
    
            // Créer un conteneur pour chaque graphique
            const graphWrapper = document.createElement('div');
            graphWrapper.classList.add('graph-wrapper');
            graphContainer.appendChild(graphWrapper);
    
            // Créer un canvas pour le graphique
            const canvas = document.createElement('canvas');
            canvas.id = `chart-${parameter}`;
            graphWrapper.appendChild(canvas);
    
            const ctx = canvas.getContext('2d');
    
            let chartType = 'line';
    
            const chart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: parameter.toUpperCase() + ` (${units[parameter]})`,
                        data: values,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)', 
                        borderColor: 'rgba(54, 162, 235, 1)', 
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Heure',
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: parameter.toUpperCase() + ` (${units[parameter]})`,
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#fff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
    
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Arial'
                                }
                            }
                        }
                    }
                }
            });
    
            const toggleButton = document.createElement('button');
            toggleButton.textContent = 'Bâtons'; // Texte initial
            toggleButton.classList.add('toggle-button');
            graphWrapper.appendChild(toggleButton);
    
            // Ajouter un gestionnaire d'événements pour basculer entre 'line' et 'bar'
            toggleButton.addEventListener('click', () => {
                if (chart.config.type === 'line') {
                    chart.config.type = 'bar';
                    toggleButton.textContent = 'Courbe';
                } else {
                    chart.config.type = 'line';
                    toggleButton.textContent = 'Bâtons';
                }
                chart.update();
            });
        });
    }       
    function displayGaugesSemaine(moyennesSemaine) {
        const gaugeContainer = document.getElementById('gauge-container');
        gaugeContainer.innerHTML = ''; // On vide le conteneur avant d'ajouter les nouvelles jauges
    
        if (moyennesSemaine && Object.keys(moyennesSemaine).length > 0) {
            // Ajouter le titre au-dessus des jauges
            const title = document.createElement('h1');
            title.classList.add('dashboard-title');
            title.textContent = 'Observations moyennes de la semaine';
            gaugeContainer.appendChild(title);
    
            const parameterLabels = {
                'temperature': 'Température',
                'vent': 'Vent',
                'humidite': 'Humidité',
                'pression': 'Pression'
            };
    
            const units = {
                'temperature': '°C',
                'vent': 'm/s',
                'humidite': '%',
                'pression': 'Pa'
            };
    
            Object.keys(moyennesSemaine).forEach(parameter => {
                const value = moyennesSemaine[parameter];
                const label = parameterLabels[parameter] || parameter;
                const unit = units[parameter] || '';
    
                // Création du conteneur principal pour la jauge
                const gaugeDiv = document.createElement('div');
                gaugeDiv.classList.add('gauge');
    
                // Création du canvas pour le graphique
                const canvas = document.createElement('canvas');
                canvas.classList.add('gauge-canvas');
                gaugeDiv.appendChild(canvas);
    
                // Création du conteneur pour la valeur et l'unité
                const valueContainer = document.createElement('div');
                valueContainer.classList.add('gauge-value');
    
                // Création des span pour la valeur
                const valueSpan = document.createElement('span');
                valueSpan.classList.add('value');
                valueSpan.textContent = value;
    
                // Création des span pour l'unité
                const unitSpan = document.createElement('span');
                unitSpan.classList.add('unit');
                unitSpan.textContent = unit;
    
                // Ajout de la valeur et de l'unité au conteneur
                valueContainer.appendChild(valueSpan);
                valueContainer.appendChild(unitSpan);
    
                // Ajout du conteneur au gaugeDiv
                gaugeDiv.appendChild(valueContainer);
    
                // Création du label
                const labelDiv = document.createElement('div');
                labelDiv.classList.add('gauge-label');
                labelDiv.textContent = label;
    
                // Ajout du label au gaugeDiv
                gaugeDiv.appendChild(labelDiv);
    
                // Ajout du gaugeDiv au conteneur principal
                gaugeContainer.appendChild(gaugeDiv);
    
                // Initialisation du graphique
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [value, 100 - value],
                            backgroundColor: ['#108439', '#E0E0E0'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        circumference: 270,
                        rotation: -135,
                        plugins: {
                            tooltip: { enabled: false },
                            legend: { display: false },
                        },
                        elements: {
                            arc: {
                                borderRadius: 5,
                            },
                        },
                    },
                });
            });
        }
    }
    function afficherTableSemaine(mesuresSemaine) {
        const tableContainer = document.getElementById('table-container');
        tableContainer.innerHTML = '';
    
        let tableHtml = '<table class="mesures-table"><thead><tr><th>Jour</th><th>Température (°C)</th><th>Vent (m/s)</th><th>Humidité (%)</th><th>Pression (Pa)</th><th>Visibilité (m)</th><th>Précipitation (mm)</th></tr></thead><tbody>';
    
        for (const date in mesuresSemaine) {
            if (mesuresSemaine.hasOwnProperty(date)) {
                const mesures = mesuresSemaine[date];
                tableHtml += `<tr><td>${date}</td><td>${mesures.temperature}</td><td>${mesures.vent}</td><td>${mesures.humidite}</td><td>${mesures.pression}</td><td>${mesures.visibilite}</td><td>${mesures.precipitation}</td></tr>`;
            }
        }
    
        tableHtml += '</tbody></table>';
        tableContainer.innerHTML = tableHtml;
    }
    function displayTempGraphSemaine(mesuresSemaine) {
        const graphContainer = document.getElementById('graph-temp');
        graphContainer.innerHTML = '';
    
        // Vérifier si le tableau 'mesuresSemaine' contient des données
        if (!mesuresSemaine || Object.keys(mesuresSemaine).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher pour les graphiques.');
            graphContainer.textContent = 'Aucune donnée disponible pour les graphiques.';
            return;
        }
        document.getElementById('graphContainer').style.display = 'flex';
    
        // Supprimer le titre existant s'il y en a un
        const existingTitle = graphContainer.previousElementSibling;
        if (existingTitle && existingTitle.classList.contains('graph-title')) {
            existingTitle.remove();
        }
    
        // Créer le titre seulement s'il y a des données
        const graphTitle = document.createElement('h1');
        graphTitle.classList.add('graph-title');
        graphTitle.textContent = 'Graphique des températures de la semaine';
        graphContainer.parentNode.insertBefore(graphTitle, graphContainer);
    
        // Récupérer les jours de la semaine
        const jours = Object.keys(mesuresSemaine);
    
        // Récupérer les valeurs de tempMax, tempMin et moyenneTemp
        const tempMax = jours.map(jour => mesuresSemaine[jour] ? mesuresSemaine[jour].tempMax : null);
        const tempMin = jours.map(jour => mesuresSemaine[jour] ? mesuresSemaine[jour].tempMin : null);
        const temperature = jours.map(jour => mesuresSemaine[jour] ? mesuresSemaine[jour].temperature : null);
    
        // Créer un canvas pour le graphique
        const canvas = document.createElement('canvas');
        canvas.id = 'chart-temperature-week';
        graphContainer.appendChild(canvas); // Ajoutez directement le canvas au conteneur existant
    
      
        const ctx = canvas.getContext('2d');
    
        // Ajuster la taille du canvas pour qu'il prenne toute la largeur du conteneur
        canvas.width = graphContainer.offsetWidth;  // Largeur du conteneur
        canvas.height = graphContainer.offsetHeight;  // Hauteur du conteneur
    
        // Créer le graphique avec 3 datasets : TempMax, TempMin et TempMoy
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: jours,  // Jours de la semaine
                datasets: [
                    {
                        label: 'Température Maximale (°C)',
                        data: tempMax,  // Températures maximales
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'red',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Température Minimale (°C)',
                        data: tempMin,  // Températures minimales
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Température Moyenne (°C)',
                        data: temperature,  // Températures moyennes
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Jour',
                            font: {
                                size: 18,
                                family: 'Arial'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Température (°C)',
                            font: {
                                size: 16,
                                family: 'Arial'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 14,
                                family: 'Arial'
                            }
                        }
                    }
                }
            }
        });
    }
    
    function displayPluvioGraphSemaine(mesuresSemaine) {
        const graphContainer = document.getElementById('graph-pluvio');
        
        graphContainer.innerHTML = '';
        
        // Vérifier si le tableau 'mesuresSemaine' contient des données
        if (!mesuresSemaine || Object.keys(mesuresSemaine).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher pour le graphique des pluviométries.');
            graphContainer.textContent = 'Aucune donnée disponible pour les graphiques.';
            return;
        }
    
        // Supprimer le titre existant s'il y en a un
        const existingTitle = graphContainer.previousElementSibling;
        if (existingTitle && existingTitle.classList.contains('graph-title')) {
            existingTitle.remove();
        }
    
        // Créer le titre seulement s'il y a des données
        const graphTitle = document.createElement('h1');
        graphTitle.classList.add('graph-title');
        graphTitle.textContent = 'Graphique des pluviométries de la semaine';
        graphContainer.parentNode.insertBefore(graphTitle, graphContainer);
    
        // Récupérer les jours de la semaine
        const jours = Object.keys(mesuresSemaine);
    
        // Récupérer les valeurs de pluMax, pluMin et moyenne des pluviométries
        const pluMax = jours.map(jour => mesuresSemaine[jour] ? mesuresSemaine[jour].pluMax : null);
        const pluMin = jours.map(jour => mesuresSemaine[jour] ? mesuresSemaine[jour].pluMin : null);
        const precipitation = jours.map(jour => mesuresSemaine[jour] ? mesuresSemaine[jour].precipitation : null);
    
        // Créer un canvas pour le graphique
        const canvas = document.createElement('canvas');
        canvas.id = 'chart-temperature-week';
        graphContainer.appendChild(canvas); 
    
        const ctx = canvas.getContext('2d');
    
        const wrapperWidth = graphContainer.offsetWidth;
        const wrapperHeight = graphContainer.offsetHeight;
    
        canvas.width = wrapperWidth;
        canvas.height = wrapperHeight;
    
    
        // Créer le graphique avec 3 datasets : pluMax, pluMin et moyenne des pluviométries
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: jours,  // Jours de la semaine
                datasets: [
                    {
                        label: 'Pluviométrie Maximale (mm)',
                        data: pluMax,  // Pluviométries maximales
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'red',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Pluviométrie Minimale (mm)',
                        data: pluMin,  // Pluviométries minimales
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'Pluviométrie Moyenne (mm)',
                        data: precipitation,  // Moyenne des pluviométries
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Jour',
                            font: {
                                size: 18,
                                family: 'Arial'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Pluviométrie (mm)',
                            font: {
                                size: 16,
                                family: 'Arial'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 14,
                                family: 'Arial'
                            }
                        }
                    }
                }
            }
        });
    }
    function displayGraphsSemaine(mesuresSemaine) {
        const graphContainer = document.getElementById('graph-container');
    
        // Vérifier si le conteneur a été trouvé
        if (!graphContainer) {
            console.error('Erreur : Élément avec la classe "graph-container" non trouvé.');
            return;
        }
    
        graphContainer.innerHTML = '';
    
        // Vérifier si le tableau 'mesuresSemaine' contient des données
        if (!mesuresSemaine || Object.keys(mesuresSemaine).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher pour les graphiques.');
            graphContainer.textContent = 'Aucune donnée disponible pour les graphiques.';
            return;
        }
    
        // Définir les unités pour chaque paramètre
        const units = {
            'temperature': '°C',
            'vent': 'm/s',
            'humidite': '%',
            'pression': 'Pa'
        };
    
        // Supprimer le titre existant s'il y en a un
        const existingTitle = graphContainer.previousElementSibling;
        if (existingTitle && existingTitle.classList.contains('graph-title')) {
            existingTitle.remove();
        }
    
        // Vérifier si le tableau 'mesuresSemaine' contient des données
        if (!mesuresSemaine || Object.keys(mesuresSemaine).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher pour les graphiques.');
            graphContainer.textContent = 'Aucune donnée disponible pour les graphiques.';
            return;
        }

        // Créer le titre seulement s'il y a des données
        const graphTitle = document.createElement('h1');
        graphTitle.classList.add('graph-title');
        graphTitle.textContent = 'Graphiques des mesures de la semaine';
        graphContainer.parentNode.insertBefore(graphTitle, graphContainer);

    
        const parameters = ['temperature', 'vent', 'humidite', 'pression'];
        const jours = Object.keys(mesuresSemaine); // Récupérer les dates de la semaine
    
        parameters.forEach(parameter => {
            const labels = jours;
            const values = labels.map(jour => mesuresSemaine[jour] ? mesuresSemaine[jour][parameter] : null);
    
            // Créer un conteneur pour chaque graphique
            const graphWrapper = document.createElement('div');
            graphWrapper.classList.add('graph-wrapper');
            graphContainer.appendChild(graphWrapper);
    
            // Créer un canvas pour le graphique
            const canvas = document.createElement('canvas');
            canvas.id = `chart-${parameter}`;
            graphWrapper.appendChild(canvas);
    
            const ctx = canvas.getContext('2d');
    
            let chartType = 'bar';
    
            const chart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: parameter.toUpperCase() + ` (${units[parameter]})`,
                        data: values,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)', 
                        borderColor: 'rgba(54, 162, 235, 1)', 
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Jour',
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: parameter.toUpperCase() + ` (${units[parameter]})`,
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#fff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Arial'
                                }
                            }
                        }
                    }
                }
            });

            const toggleButton = document.createElement('button');
            toggleButton.textContent = 'Courbe'; 
            toggleButton.classList.add('toggle-button');
            graphWrapper.appendChild(toggleButton);
    
            // Ajouter un gestionnaire d'événements pour basculer entre 'line' et 'bar'
            toggleButton.addEventListener('click', () => {
                if (chart.config.type === 'bar') {
                    chart.config.type = 'line';
                    toggleButton.textContent = 'Bâtons';
                } else {
                    chart.config.type = 'bar';
                    toggleButton.textContent = 'Courbe';
                }
                chart.update();
            });
        });
    }   

    function displayGaugesMois(moyennesMois) {
        const gaugeContainer = document.getElementById('gauge-container');
        gaugeContainer.innerHTML = '';

        if (moyennesMois && Object.keys(moyennesMois).length > 0) {
            const title = document.createElement('h1');
            title.classList.add('dashboard-title');
            title.textContent = 'Observations moyennes du mois';
            gaugeContainer.appendChild(title);

            const parameterLabels = {
                'temperature': 'Température',
                'vent': 'Vent',
                'humidite': 'Humidité',
                'pression': 'Pression'
            };

            const units = {
                'temperature': '°C',
                'vent': 'm/s',
                'humidite': '%',
                'pression': 'Pa'
            };

            Object.keys(moyennesMois).forEach(parameter => {
                const value = moyennesMois[parameter];
                const label = parameterLabels[parameter] || parameter;
                const unit = units[parameter] || '';

                const gaugeDiv = document.createElement('div');
                gaugeDiv.classList.add('gauge');

                const canvas = document.createElement('canvas');
                canvas.classList.add('gauge-canvas');
                gaugeDiv.appendChild(canvas);

                const valueContainer = document.createElement('div');
                valueContainer.classList.add('gauge-value');

                const valueSpan = document.createElement('span');
                valueSpan.classList.add('value');
                valueSpan.textContent = value;

                const unitSpan = document.createElement('span');
                unitSpan.classList.add('unit');
                unitSpan.textContent = unit;

                valueContainer.appendChild(valueSpan);
                valueContainer.appendChild(unitSpan);
                gaugeDiv.appendChild(valueContainer);

                const labelDiv = document.createElement('div');
                labelDiv.classList.add('gauge-label');
                labelDiv.textContent = label;
                gaugeDiv.appendChild(labelDiv);

                gaugeContainer.appendChild(gaugeDiv);

                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [value, 100 - value],
                            backgroundColor: ['#108439', '#E0E0E0'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        circumference: 270,
                        rotation: -135,
                        plugins: {
                            tooltip: { enabled: false },
                            legend: { display: false },
                        },
                        elements: {
                            arc: {
                                borderRadius: 5,
                            },
                        },
                    },
                });
            });
        }
    }

    function displayTableMois(mesuresMois) {
        const tableContainer = document.getElementById('table-container');
        tableContainer.innerHTML = '';

        if (!mesuresMois || Object.keys(mesuresMois).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher dans le tableau.');
            tableContainer.textContent = 'Aucune donnée disponible pour le tableau.';
            return;
        }

        const table = document.createElement('table');
        table.classList.add('mesures-table');

        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        const headers = ['Date', 'Température (°C)', 'Vent (m/s)', 'Humidité (%)', 'Pression (Pa)', 'Visibilité(m)', 'Précipitation(mm)'];
        headers.forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        Object.keys(mesuresMois).forEach(date => {
            const mesure = mesuresMois[date];
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${date}</td>
                <td>${mesure.temperature}</td>
                <td>${mesure.vent}</td>
                <td>${mesure.humidite}</td>
                <td>${mesure.pression}</td>
                <td>${mesure.visibilite}</td>
                <td>${mesure.precipitation}</td>
            `;
            tbody.appendChild(row);
        });
        table.appendChild(tbody);

        tableContainer.appendChild(table);
    }

    function displayGraphsMois(mesuresMois) {
        const graphContainer = document.getElementById('graph-container');
        graphContainer.innerHTML = '';

        if (!mesuresMois || Object.keys(mesuresMois).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher pour les graphiques.');
            graphContainer.textContent = 'Aucune donnée disponible pour les graphiques.';
            return;
        }

        const units = {
            'temperature': '°C',
            'vent': 'm/s',
            'humidite': '%',
            'pression': 'Pa'
        };

        const existingTitle = graphContainer.previousElementSibling;
        if (existingTitle && existingTitle.classList.contains('graph-title')) {
            existingTitle.remove();
        }

        const graphTitle = document.createElement('h1');
        graphTitle.classList.add('graph-title');
        graphTitle.textContent = 'Graphiques des mesures du mois';
        graphContainer.parentNode.insertBefore(graphTitle, graphContainer);

        const parameters = ['temperature', 'vent', 'humidite', 'pression'];
        const dates = Object.keys(mesuresMois);

        parameters.forEach(parameter => {
            const labels = dates;
            const values = labels.map(date => mesuresMois[date] ? mesuresMois[date][parameter] : null);

            const graphWrapper = document.createElement('div');
            graphWrapper.classList.add('graph-wrapper');
            graphContainer.appendChild(graphWrapper);

            const canvas = document.createElement('canvas');
            canvas.id = `chart-${parameter}`;
            graphWrapper.appendChild(canvas);

            const ctx = canvas.getContext('2d');

            let chartType = 'line';

            const chart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: parameter.toUpperCase() + ` (${units[parameter]})`,
                        data: values,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date',
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: parameter.toUpperCase() + ` (${units[parameter]})`,
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#fff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Arial'
                                }
                            }
                        }
                    }
                }
            });

            const toggleButton = document.createElement('button');
            toggleButton.textContent = 'Bâtons';
            toggleButton.classList.add('toggle-button');
            graphWrapper.appendChild(toggleButton);

            toggleButton.addEventListener('click', () => {
                if (chart.config.type === 'line') {
                    chart.config.type = 'bar';
                    toggleButton.textContent = 'Courbe';
                } else {
                    chart.config.type = 'line';
                    toggleButton.textContent = 'Bâtons';
                }
                chart.update();
            });
        });
    }

    function displayGaugesAnnee(moyennesAnnee) {
        const gaugeContainer = document.getElementById('gauge-container');
        gaugeContainer.innerHTML = '';

        if (moyennesAnnee && Object.keys(moyennesAnnee).length > 0) {
            const title = document.createElement('h1');
            title.classList.add('dashboard-title');
            title.textContent = 'Observations moyennes de l\'année';
            gaugeContainer.appendChild(title);

            const parameterLabels = {
                'temperature': 'Température',
                'vent': 'Vent',
                'humidite': 'Humidité',
                'pression': 'Pression'
            };

            const units = {
                'temperature': '°C',
                'vent': 'm/s',
                'humidite': '%',
                'pression': 'Pa'
            };

            Object.keys(moyennesAnnee).forEach(parameter => {
                const value = moyennesAnnee[parameter];
                const label = parameterLabels[parameter] || parameter;
                const unit = units[parameter] || '';

                const gaugeDiv = document.createElement('div');
                gaugeDiv.classList.add('gauge');

                const canvas = document.createElement('canvas');
                canvas.classList.add('gauge-canvas');
                gaugeDiv.appendChild(canvas);

                const valueContainer = document.createElement('div');
                valueContainer.classList.add('gauge-value');

                const valueSpan = document.createElement('span');
                valueSpan.classList.add('value');
                valueSpan.textContent = value;

                const unitSpan = document.createElement('span');
                unitSpan.classList.add('unit');
                unitSpan.textContent = unit;

                valueContainer.appendChild(valueSpan);
                valueContainer.appendChild(unitSpan);
                gaugeDiv.appendChild(valueContainer);

                const labelDiv = document.createElement('div');
                labelDiv.classList.add('gauge-label');
                labelDiv.textContent = label;
                gaugeDiv.appendChild(labelDiv);

                gaugeContainer.appendChild(gaugeDiv);

                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [value, 100 - value],
                            backgroundColor: ['#108439', '#E0E0E0'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        circumference: 270,
                        rotation: -135,
                        plugins: {
                            tooltip: { enabled: false },
                            legend: { display: false },
                        },
                        elements: {
                            arc: {
                                borderRadius: 5,
                            },
                        },
                    },
                });
            });
        }
    }

    function displayTableAnnee(mesuresAnnee) {
        const tableContainer = document.getElementById('table-container');
        tableContainer.innerHTML = '';

        if (!mesuresAnnee || Object.keys(mesuresAnnee).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher dans le tableau.');
            tableContainer.textContent = 'Aucune donnée disponible pour le tableau.';
            return;
        }

        const table = document.createElement('table');
        table.classList.add('mesures-table');

        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        const headers = ['Date', 'Température (°C)', 'Vent (m/s)', 'Humidité (%)', 'Pression (Pa)', 'Visibilité(m)', 'Précipitation(mm)'];
        headers.forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        Object.keys(mesuresAnnee).forEach(date => {
            const mesure = mesuresAnnee[date];
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${date}</td>
                <td>${mesure.temperature}</td>
                <td>${mesure.vent}</td>
                <td>${mesure.humidite}</td>
                <td>${mesure.pression}</td>
                <td>${mesure.visibilite}</td>
                <td>${mesure.precipitation}</td>
            `;
            tbody.appendChild(row);
        });
        table.appendChild(tbody);

        tableContainer.appendChild(table);
    }

    function displayGraphsAnnee(mesuresAnnee) {
        const graphContainer = document.getElementById('graph-container');
        graphContainer.innerHTML = '';

        if (!mesuresAnnee || Object.keys(mesuresAnnee).length === 0) {
            console.warn('Avertissement : Aucune donnée à afficher pour les graphiques.');
            graphContainer.textContent = 'Aucune donnée disponible pour les graphiques.';
            return;
        }

        const units = {
            'temperature': '°C',
            'vent': 'm/s',
            'humidite': '%',
            'pression': 'Pa'
        };

        const existingTitle = graphContainer.previousElementSibling;
        if (existingTitle && existingTitle.classList.contains('graph-title')) {
            existingTitle.remove();
        }

        const graphTitle = document.createElement('h1');
        graphTitle.classList.add('graph-title');
        graphTitle.textContent = 'Graphiques des mesures de l\'année';
        graphContainer.parentNode.insertBefore(graphTitle, graphContainer);

        const parameters = ['temperature', 'vent', 'humidite', 'pression'];
        const months = Object.keys(mesuresAnnee);

        parameters.forEach(parameter => {
            const labels = months;
            const values = labels.map(month => mesuresAnnee[month] ? mesuresAnnee[month][parameter] : null);

            const graphWrapper = document.createElement('div');
            graphWrapper.classList.add('graph-wrapper');
            graphContainer.appendChild(graphWrapper);

            const canvas = document.createElement('canvas');
            canvas.id = `chart-${parameter}`;
            graphWrapper.appendChild(canvas);

            const ctx = canvas.getContext('2d');

            let chartType = 'line';

            const chart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: parameter.toUpperCase() + ` (${units[parameter]})`,
                        data: values,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Mois',
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: parameter.toUpperCase() + ` (${units[parameter]})`,
                                font: {
                                    size: 16,
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#fff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Arial'
                                }
                            }
                        }
                    }
                }
            });

            const toggleButton = document.createElement('button');
            toggleButton.textContent = 'Bâtons';
            toggleButton.classList.add('toggle-button');
            graphWrapper.appendChild(toggleButton);

            toggleButton.addEventListener('click', () => {
                if (chart.config.type === 'line') {
                    chart.config.type = 'bar';
                    toggleButton.textContent = 'Courbe';
                } else {
                    chart.config.type = 'line';
                    toggleButton.textContent = 'Bâtons';
                }
                chart.update();
            });
        });
    }
});
