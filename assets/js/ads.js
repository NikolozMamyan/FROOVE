document.addEventListener('turbo:load', function () {
    const participateButtons = document.querySelectorAll('.participate-btn');

    participateButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const adId = btn.getAttribute('data-ad-id');
            const modalMessageContainer = document.getElementById('modalMessageContainer-' + adId);

            fetch('/ads/' + adId + '/participate', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(error => {
                            throw new Error(error.error || 'Une erreur est survenue.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayMessageInModal('Participation réussie !', 'success', modalMessageContainer);
                        setTimeout(() => window.location.reload(), 2000); // Recharge la page après 2 secondes
                    } else {
                        displayMessageInModal(data.error || 'Une erreur est survenue.', 'error', modalMessageContainer);
                    }
                })
                .catch(err => {
                    console.error(err);
                    displayMessageInModal(err.message || 'Erreur de communication avec le serveur.', 'error', modalMessageContainer);
                });
        });
    });

    function displayMessageInModal(message, type, container) {
        container.innerHTML = ''; // Efface les anciens messages
        const messageBox = document.createElement('div');
        messageBox.className = `modal-message-box ${type}`;
        messageBox.textContent = message;

        // Styles inline pour les messages (facultatif, déjà couvert par le CSS ci-dessous)
        messageBox.style.padding = '10px';
        messageBox.style.marginBottom = '10px';
        messageBox.style.borderRadius = '5px';

        if (type === 'success') {
            messageBox.style.backgroundColor = '#4caf50';
            messageBox.style.color = '#fff';
        } else if (type === 'error') {
            messageBox.style.backgroundColor = '#f44336';
            messageBox.style.color = '#fff';
        }

        container.appendChild(messageBox);

        // Optionnel : Efface le message après 5 secondes
        setTimeout(() => {
            messageBox.remove();
        }, 5000);
    }
});
