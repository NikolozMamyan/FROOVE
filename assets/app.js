import { Application } from '@hotwired/stimulus';
import ParticipateController from './controller/participate_controller.js';
import PasswordVisibilityController from './controller/password_visibility_controller.js'; // Ajout

const application = Application.start();
application.register('participate', ParticipateController);
application.register('password-visibility', PasswordVisibilityController); // Enregistrement du contrôleur
