document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('dropdown-search');
    const dropdownOptions = document.getElementById('dropdown-options');
    const stationHidden = document.getElementById('station-hidden');

    // Afficher le menu déroulant dès que l'utilisateur clique dans le champ de recherche
    searchInput.addEventListener('focus', () => {
        dropdownOptions.classList.add('visible'); // Afficher le menu déroulant
    });

    // Afficher les options de station en fonction de la recherche
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase();
        const dropdownItems = document.querySelectorAll('.dropdown-item');

        dropdownItems.forEach(item => {
            const text = item.textContent.trim().toLowerCase();
            item.style.display = text.startsWith(query) ? 'block' : 'none';
        });
    });

    // Sélectionner une station
    dropdownOptions.addEventListener('click', (event) => {
        const item = event.target.closest('.dropdown-item');
        if (item) {
            const value = item.getAttribute('data-value');
            const text = item.textContent.trim();

            // Mettre à jour le champ de recherche et la valeur cachée
            searchInput.value = text;
            stationHidden.value = value;

            // Cacher la liste des options après sélection
            dropdownOptions.classList.remove('visible');
        }
    });
});
