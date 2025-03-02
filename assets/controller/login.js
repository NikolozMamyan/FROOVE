import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["formWrapper", "switchBtn", "formTitle", "switchText"];

    connect() {
        this.isLogin = true;
    }

    switch(event) {
        event.preventDefault();
        this.formWrapperTarget.classList.toggle('flipped');
        this.isLogin = !this.isLogin;

        this.formTitleTarget.textContent = this.isLogin ? 'Connexion' : 'Créer un compte';
        this.switchTextTarget.textContent = this.isLogin ? 'Pas encore de compte ?' : 'Déjà un compte ?';
        this.switchBtnTarget.textContent = this.isLogin ? 'S\'inscrire' : 'Se connecter';
    }
}
