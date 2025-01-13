document.addEventListener('DOMContentLoaded', function() {
    
    const formWrapper = document.getElementById('formWrapper');
    const switchBtn = document.getElementById('switchBtn');
    const formTitle = document.getElementById('formTitle');
    const switchText = document.getElementById('switchText');
    let isLogin = true;

    switchBtn.addEventListener('click', function(event) {
        event.preventDefault();
        formWrapper.classList.toggle('flipped');
        isLogin = !isLogin;

        if (isLogin) {
            formTitle.textContent = 'Connexion';
            switchText.textContent = 'Pas encore de compte ?';
            switchBtn.textContent = 'S\'inscrire';
        } else {
            formTitle.textContent = 'Créer un compte';
            switchText.textContent = 'Déjà un compte ?';
            switchBtn.textContent = 'Se connecter';
        }
    });
});