document.addEventListener('DOMContentLoaded', () => {
    const searchBar = document.getElementById('search-bar');
    const suggestionsContainer = document.getElementById('suggestions');
    const meteothequeContainer = document.getElementById('meteotheque-container');
    const stationsList = document.getElementById('stationsList');
    let allStations = [];

    // Charger toutes les stations au démarrage
    loadAllStations();

    searchBar.addEventListener('input', debounce(() => {
        const query = searchBar.value.trim().toLowerCase();
        if (query.length > 0) {
            const filteredStations = filterStations(query);
            displaySuggestions(filteredStations);
            fetchMeteothequesParStation(query);
        } else {
            suggestionsContainer.innerHTML = '';
            resetMeteotheques();
        }
    }, 300));

    // Fermer les suggestions si on clique en dehors
    document.addEventListener('click', (e) => {
        if (e.target !== searchBar && e.target !== suggestionsContainer) {
            suggestionsContainer.innerHTML = '';
        }
    });

    function loadAllStations() {
        const stationElements = stationsList.querySelectorAll('.options');
        stationElements.forEach(el => {
            const [num_station, nom] = el.dataset.value.split('|');
            allStations.push({ num_station, nom });
        });
    }

    function filterStations(query) {
        // Filtrer uniquement les stations qui commencent par la saisie
        return allStations.filter(station =>
            station.nom.toLowerCase().startsWith(query) || 
            station.num_station.startsWith(query)
        );
    }

    function displaySuggestions(suggestions) {
        suggestionsContainer.innerHTML = '';
        suggestions.forEach(station => {
            const suggestion = document.createElement('div');
            suggestion.className = 'suggestion';
            suggestion.textContent = `${station.nom} - ${station.num_station}`;
            suggestion.addEventListener('click', () => {
                searchBar.value = `${station.nom} - ${station.num_station}`;
                suggestionsContainer.innerHTML = '';
                fetchMeteothequesParStation(station.num_station);
            });
            suggestionsContainer.appendChild(suggestion);
        });
    }

    function fetchMeteothequesParStation(query) {
        fetch(`meteothequeVisiteur.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    filterMeteotheques(data);
                } else {
                    console.error("Erreur serveur ou format inattendu :", data);
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    function filterMeteotheques(meteothequesData) {
        meteothequeContainer.innerHTML = ''; // Réinitialiser l'affichage
        
        if (meteothequesData.length === 0) {
            // Créer un message lorsqu'aucune météothèque n'est trouvée
            const noResultMessage = document.createElement('p');
            noResultMessage.textContent = 'Aucune météothèque trouvée pour cette station.';
            noResultMessage.style.textAlign = 'center';
            noResultMessage.style.color = '#de2d26';
            noResultMessage.style.fontFamily = 'Quicksand, sans-serif';
            noResultMessage.style.fontSize = '18px';
            noResultMessage.style.margin = '20px 0';
            noResultMessage.style.fontWeight ='bold';
            
            meteothequeContainer.appendChild(noResultMessage);
        } else {
            // Afficher les météothèques comme avant
            meteothequesData.forEach(m => {
                const block = document.createElement('div');
                block.className = 'meteotheque-block';
                block.innerHTML = `
                    <span class="user-name top-right">${m.username}</span>
                    <p>Visualisez la météothèque de ${m.prenom} ${m.nom}</p>               
                    <a class="ghost" href="detailsMeteotheque.php?meteotheque_id=${m.meteotheque_id}&user_id=${m.user_id}">Découvrir</a>`;
                meteothequeContainer.appendChild(block);
            });
        }
    }
    
    // Fonction pour recharger la page pour afficher toutes les météothèques initiales
    function resetMeteotheques() {
        location.reload(); 
    }

    function debounce(func, delay) {
        let debounceTimer;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };
    }
});
