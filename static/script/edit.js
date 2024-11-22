// Met à jour le champ texte avec le nom du fichier sélectionné
function updateFileName() {
    const fileInput = document.getElementById('photo_profil');
    const fileName = fileInput.files[0] ? fileInput.files[0].name : 'Aucune photo choisie';
    document.getElementById('file_name').textContent = fileName;
}

// Marque la photo pour suppression
function deletePhoto() {
    document.getElementById('delete_photo').value = '1'; 
    document.getElementById('file_name').textContent = 'Aucune photo choisie'; 
    document.getElementById('photo_profil').value = ''; 
}