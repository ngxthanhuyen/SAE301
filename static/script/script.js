const registrationButton = document.getElementById("register");
const loginButton = document.getElementById("login");
const container = document.getElementById("container");

registrationButton.addEventListener("click", ()=>{
    container.classList.add("right-panel-active");
});

loginButton.addEventListener("click", ()=>{
    container.classList.remove("right-panel-active");
});

//On sélectionne tous les éléments avec la classe "oeil"
const oeils = document.querySelectorAll('.oeil');

//On ajoute un gestionnaire d'événements à chaque icône œil
oeils.forEach(function(oeil) {
    oeil.addEventListener('click', function() {
        //On récupère le champ de mot de passe associé
        const passwordInput = this.previousElementSibling;

        //On bascule le type entre 'password' et 'text'
        const type = passwordInput.getAttribute('type') == 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        //On change l'image en fonction de l'état du champ de mot de passe
        if (type === 'password') {
            this.querySelector('img').src = 'https://cdn-icons-png.freepik.com/256/12197/12197891.png?semt=ais_hybrid';
            this.querySelector('img').alt = 'Afficher le mot de passe';
        } else {
            this.querySelector('img').src = 'https://cdn-icons-png.flaticon.com/128/797/797403.png';
            this.querySelector('img').alt = 'Masquer le mot de passe';
        }
    });
});
