document.addEventListener('turbo:load', function() {
    const formWrapper = document.getElementById('formWrapper');
    const switchBtn = document.getElementById('switchBtn');
    const formTitle = document.getElementById('formTitle');
    const formSubtitle = document.getElementById('formSubtitle');
    const switchText = document.getElementById('switchText');
    let isLogin = true;

    switchBtn.addEventListener('click', function(event) {
        event.preventDefault();
        formWrapper.classList.toggle('flipped');
        isLogin = !isLogin;

        if (isLogin) {
            formTitle.textContent = 'Connexion';
            formSubtitle.textContent = 'Ravi de vous revoir !';
            switchText.textContent = 'Pas encore de compte ?';
            switchBtn.textContent = 'S\'inscrire';
        } else {
            formTitle.textContent = 'Créer un compte';
            formSubtitle.textContent = 'Rejoignez-nous dès maintenant';
            switchText.textContent = 'Déjà un compte ?';
            switchBtn.textContent = 'Se connecter';
        }
    });
});