document.addEventListener('DOMContentLoaded', function () {
    const favoriteButton = document.getElementById('favorite-button');
    const modal = document.getElementById('confirmation-modal');
    const modalMessage = document.getElementById('modal-message');
    const modalYes = document.getElementById('modal-yes');
    const modalNo = document.getElementById('modal-no');

    let isFavorite = favoriteButton.dataset.favorite === 'true';
    const stationId = favoriteButton.dataset.numStation;

    const updateButtonAppearance = () => {
        favoriteButton.classList.toggle('active', isFavorite);
        favoriteButton.style.backgroundColor = isFavorite ? 'pink' : 'purple';
        modalMessage.textContent = isFavorite
            ? "Souhaitez-vous supprimer cette station de vos favoris ?"
            : "Souhaitez-vous ajouter cette station en favoris ?";
    };

    const showModal = () => modal.classList.remove('hidden');
    const closeModal = () => modal.classList.add('hidden');

    favoriteButton.addEventListener('click', showModal);
    modalNo.addEventListener('click', closeModal);

    modalYes.addEventListener('click', () => {
        // Recharge la page avec le paramètre toggleFavorite
        window.location.href = `?page=StationsInfos&num_station=${stationId}&action=toggleFavorite`;
    });

    document.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    updateButtonAppearance();
     // Gestion des listes déroulantes
     document.querySelectorAll('.select-input').forEach(input => {
        input.addEventListener('click', (event) => {
            const optionsList = input.nextElementSibling;
            optionsList.style.display = optionsList.style.display === 'block' ? 'none' : 'block';
            event.stopPropagation();
        });
    });

    // Sélection d'une option
    document.querySelectorAll('.options').forEach(option => {
        option.addEventListener('click', (event) => {
            const selectedValue = event.target.dataset.value;
            const input = event.target.closest('.select-wrapper').querySelector('.select-input');
            input.textContent = event.target.textContent; 
            input.nextElementSibling.style.display = 'none';
        });
    });

    // Ferme les listes déroulantes en cliquant à l'extérieur
    document.addEventListener('click', () => {
        document.querySelectorAll('.options-list').forEach(list => {
            list.style.display = 'none';
        });
    });
    const compareForm = document.getElementById('compareForm');

    // Gestion des clics sur les options des listes déroulantes
    document.querySelectorAll('.options-list li').forEach(option => {
        option.addEventListener('click', (event) => {
            const value = event.target.dataset.value;
            if (!value) {
                console.error("La valeur de l'option sélectionnée est vide ou invalide.");
                return;
            }

            // Sépare `num_station` et `nom_station`
            const [stationNum, stationName] = value.split('|');

            const wrapper = event.target.closest('.select-wrapper');
            if (!wrapper) {
                console.error("Aucun conteneur 'select-wrapper' trouvé pour cette option.");
                return;
            }

            // Trouve l'élément `select-input` associé
            const input = wrapper.querySelector('.select-input');
            if (input) {
                input.textContent = `${stationName} - ${stationNum}`; 
            } else {
                console.error("Aucun élément '.select-input' trouvé dans le conteneur.");
            }

            // Trouve l'input caché associé et met à jour sa valeur
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                hiddenInput.value = stationNum;
            } else {
                console.error("Aucun input caché trouvé dans le conteneur.");
            }

            // Ferme la liste déroulante
            const optionsList = wrapper.querySelector('.options-list');
            if (optionsList) {
                optionsList.style.display = 'none';
            }
        });
    });

    // Gestion de la sélection de l'heure
    document.querySelectorAll('#heure-list li').forEach(option => {
        option.addEventListener('click', function () {
            const value = this.getAttribute('data-value');
            // On met à jour l'input caché pour l'heure
            document.getElementById('heure-hidden').value = value; 
            // On met à jour le texte affiché
            document.getElementById('heure-select').textContent = this.textContent; 
        });
    });

    // Gestion de la soumission du formulaire
    compareForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // On crée un objet FormData avec les données du formulaire
        const formData = new FormData(this); 

        // Vérifie si des champs requis sont vides
        const requiredFields = ['station1', 'station2', 'date_selection', 'heure_selection'];
        for (let field of requiredFields) {
            if (!formData.get(field)) {
                alert(`Le champ ${field} est requis.`);
                return;
            }
        }

        // Affichage des choix de l'utilisateur
        displayUserChoices({
            'Station 1': document.querySelector('#station1-select').textContent,
            'Station 2': document.querySelector('#station2-select').textContent,
            'Date sélectionnée': formData.get('date_selection'),
            'Heure sélectionnée': formData.get('heure_selection'),
        });

        const stationId = document.getElementById('favorite-button').getAttribute('data-num-station');

        fetch(`?page=StationsInfos&num_station=${stationId}`, { 
            method: 'POST',
            body: formData, 
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur réseau : ${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then(text => {
                console.log(text);  
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        updateComparisonTable(data.result);
                        // Ajout du défilement vers la section du tableau
                        const comparisonSection = document.getElementById('user-choices');
                        comparisonSection.classList.remove('hidden'); // Assurez-vous que la section est visible
                        comparisonSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        alert(data.message || 'Erreur lors de la récupération des données.');
                    }
                } catch (error) {
                    console.error('Erreur lors du parsing JSON :', error);
                    console.log('Données reçues (non-parsées) :', text);
                    alert('Erreur de parsing JSON. Veuillez vérifier les données reçues.');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la requête fetch :', error.message);
                alert('Une erreur est survenue lors de la comparaison. Vérifiez votre connexion ou contactez un administrateur.');
            });        
    });

    // Fonction pour afficher les choix de l'utilisateur
    function displayUserChoices(data) {
        // Mise à jour des éléments HTML pour chaque choix
        document.getElementById('choice-station1').textContent = data['Station 1'];
        document.getElementById('choice-station2').textContent = data['Station 2'];
        document.getElementById('choice-date').textContent = data['Date sélectionnée'];
        document.getElementById('choice-time').textContent = data['Heure sélectionnée'];
    
        // Affiche la section des choix
        document.getElementById('user-choices').classList.remove('hidden');
    }
    

    // Fonction pour mettre à jour le tableau de comparaison
    function updateComparisonTable(data) {
        const parameters = Object.keys(data); // Les paramètres (ex: Temperature, Humidite...)

        parameters.forEach(param => {
            const paramLower = param.toLowerCase();
            const station1Value = data[param].station1 || 0; 
            const station2Value = data[param].station2 || 0;  
            const ecartValue = data[param].ecart || 0;         
            const unit = data[param].unite || '';              

            // Mise à jour des cellules avec les valeurs et les unités
            updateTableCell(`${paramLower}-station1`, station1Value, unit);
            updateTableCell(`${paramLower}-station2`, station2Value, unit);
            updateTableCell(`${paramLower}-ecart`, ecartValue, unit);
        });
    }

    // Fonction pour mettre à jour une cellule du tableau
    function updateTableCell(elementId, value, unit = '') {
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Élément introuvable : ${elementId}`);
            return;
        }

        // Mise à jour du contenu de la cellule avec la valeur et l'unité
        const formattedValue = typeof value === 'number' ? new Intl.NumberFormat().format(value) : value;
        element.textContent = `${formattedValue} ${unit}`.trim();
    }
    const saveComparisonForm = document.getElementById('saveComparisonForm');
    
    document.getElementById('submit-save').addEventListener('click', function() {

        // Remplir les champs cachés avec les valeurs du tableau
        document.getElementById('save-date').value = document.getElementById('choice-date').textContent;
        document.getElementById('save-time').value = document.getElementById('choice-time').textContent;
        document.getElementById('save-station1').value = document.getElementById('choice-station1').textContent;
        document.getElementById('save-station2').value = document.getElementById('choice-station2').textContent;
        document.getElementById('save-temp-s1').value = document.getElementById('temperature-station1').textContent;
        document.getElementById('save-temp-s2').value = document.getElementById('temperature-station2').textContent;
        document.getElementById('save-temp-ec').value = document.getElementById('temperature-ecart').textContent;
        document.getElementById('save-hum-s1').value = document.getElementById('humidite-station1').textContent;
        document.getElementById('save-hum-s2').value = document.getElementById('humidite-station2').textContent;
        document.getElementById('save-hum-ec').value = document.getElementById('humidite-ecart').textContent;
        document.getElementById('save-prec-s1').value = document.getElementById('precipitation-station1').textContent;
        document.getElementById('save-prec-s2').value = document.getElementById('precipitation-station2').textContent;
        document.getElementById('save-prec-ec').value = document.getElementById('precipitation-ecart').textContent;
        document.getElementById('save-vent-s1').value = document.getElementById('vent-station1').textContent;
        document.getElementById('save-vent-s2').value = document.getElementById('vent-station2').textContent;
        document.getElementById('save-vent-ec').value = document.getElementById('vent-ecart').textContent;
        document.getElementById('save-press-s1').value = document.getElementById('pression-station1').textContent;
        document.getElementById('save-press-s2').value = document.getElementById('pression-station2').textContent;
        document.getElementById('save-press-ec').value = document.getElementById('pression-ecart').textContent;
        document.getElementById('save-vis-s1').value = document.getElementById('visibilite-station1').textContent;
        document.getElementById('save-vis-s2').value = document.getElementById('visibilite-station2').textContent;
        document.getElementById('save-vis-ec').value = document.getElementById('visibilite-ecart').textContent;

        //fetch pour envoyer les données au serveur
        fetch('', { // Envoie les données à l'URL actuelle
            method: 'POST',
            body: new URLSearchParams(new FormData(saveComparisonForm))
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Erreur:', error);
        });
    });
    
});





