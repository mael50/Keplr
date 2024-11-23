import './bootstrap.js';
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
console.log('You can import CSS files too!');

if ('serviceWorker' in navigator && 'PushManager' in window) {
    let swRegistration = null;

    // Demander d'abord la permission pour les notifications
    Notification.requestPermission()
        .then(permission => {
            if (permission === 'granted') {
                return navigator.serviceWorker.register('/sw.js');
            } else {
                throw new Error('Permission for notifications was denied');
            }
        })
        .then(registration => {
            console.log('Service Worker registered with scope:', registration.scope);
            swRegistration = registration;

            // Vérifier si l'abonnement existe déjà
            return swRegistration.pushManager.getSubscription();
        })
        .then(subscription => {
            if (subscription) {
                console.log('User is already subscribed');
                return subscription; // Utiliser l'abonnement existant
            }

            // Créer un nouvel abonnement uniquement si aucun n'existe
            return swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array('BEhGplSNE_lmI07MuwyIMb5IN53Exd8DPsEqwdLrfjBNhCMrSb87yCHZ5E7cHtIwMrpvFhoWZXsf5zUb2xZ5dXs')
            });
        })
        .then(subscription => {
            console.log('Subscription details:', subscription);

            return fetch('/save-subscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subscription)
            });
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to save subscription');
            }
            console.log('Subscription saved on server.');
        })
        .catch(error => {
            console.error('Error during subscription process:', error);
        });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return new Uint8Array([...rawData].map(char => char.charCodeAt(0)));
}