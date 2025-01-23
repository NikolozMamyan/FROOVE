document.addEventListener('DOMContentLoaded', function () {
    const participateButtons = document.querySelectorAll('.participate-btn');

    participateButtons.forEach(btn => {
        btn.addEventListener('click', async function (event) {
            event.preventDefault(); 

            const adId = this.getAttribute('data-ad-id');
            const likeImg = this.querySelector('img');
            
            try {
                // Animation de pulsation
                likeImg.classList.add('like-animation');
                likeImg.classList.toggle('liked');
                
                // Appel asynchrone
                const response = await fetch(`/web/ads/${adId}/participate`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                });

                // Vérifie la réponse
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Une erreur est survenue.');
                }

                const data = await response.json();

                // Recharge la page si nécessaire
                if (data.success) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
            } catch (error) {
                console.error('Erreur de participation:', error);
            } finally {
                // Restaure l'animation
                setTimeout(() => {
                    likeImg.classList.remove('like-animation');
                }, 500);
            }
        });
    });
});