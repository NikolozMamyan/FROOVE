import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["password", "toggle"];

    connect() {
        if (!this.hasPasswordTarget || !this.hasToggleTarget) {
            console.warn("PasswordVisibilityController: Les cibles ne sont pas définies correctement.");
        }
    }

    toggleVisibility(event) {
        event.preventDefault();
        const type = this.passwordTarget.getAttribute('type') === 'password' ? 'text' : 'password';
        this.passwordTarget.setAttribute('type', type);

        // Basculer les classes de l'icône (ajouter fa-eye-slash quand le mot de passe est visible)
        this.toggleTarget.classList.toggle('fa-eye');
        this.toggleTarget.classList.toggle('fa-eye-slash');
    }
}
