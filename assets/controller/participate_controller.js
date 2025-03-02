// assets/controllers/participate_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['likeImg'];

  async send(event) {
    event.preventDefault();

    // Récupère l'ID de l'annonce à partir de l'attribut data-ad-id du bouton
    const adId = this.element.dataset.adId;
    // Récupère l'image de l'icône depuis le target ou directement le premier <img> du bouton
    const likeImg = this.hasLikeImgTarget ? this.likeImgTarget : this.element.querySelector('img');

    // Animation de pulsation et basculement de l'état "liked"
    likeImg.classList.add('like-animation');
    likeImg.classList.toggle('liked');

    try {
      // Appel asynchrone vers le serveur
      const response = await fetch(`/web/ads/${adId}/participate`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
        },
      });

      // Vérification de la réponse
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Une erreur est survenue.');
      }

      const data = await response.json();

      // Recharge la page si l'opération est un succès
      if (data.success) {
        setTimeout(() => {
          window.location.reload();
        }, 500);
      }
    } catch (error) {
      console.error('Erreur de participation:', error);
    } finally {
      // Retire l'animation après 500ms
      setTimeout(() => {
        likeImg.classList.remove('like-animation');
      }, 500);
    }
  }
}
