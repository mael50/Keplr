import './bootstrap.js';
import './styles/app.css';

// Fonction pour vérifier si l'utilisateur est connecté
async function isLoggedIn() {
    try {
        const response = await fetch('/api/user-status');
        const data = await response.json();
        return data.isLoggedIn;
    } catch (error) {
        console.error('Erreur lors de la vérification de connexion:', error);
        return false;
    }
}

// Fonction pour initialiser les notifications
async function initializeNotifications() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.log('Les notifications push ne sont pas supportées');
        return;
    }

    // Vérifie d'abord si l'utilisateur est connecté
    const loggedIn = await isLoggedIn();
    if (!loggedIn) {
        console.log('Utilisateur non connecté, notifications désactivées');
        return;
    }

    let swRegistration = null;

    try {
        // Sur mobile, on attend une interaction utilisateur
        if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            const subscribeButton = document.getElementById('notificationSubscribeBtn');
            if (!subscribeButton) {
                // Créer le bouton s'il n'existe pas
                const btn = document.createElement('button');
                btn.id = 'notificationSubscribeBtn';
                btn.textContent = 'Activer les notifications';
                btn.classList.add('btn', 'btn-primary', 'mt-3');
                document.querySelector('.container').appendChild(btn);

                btn.addEventListener('click', async () => {
                    await requestAndSubscribe();
                });
            }
        } else {
            // Sur desktop, on peut demander directement
            await requestAndSubscribe();
        }
    } catch (error) {
        console.error('Erreur lors de l\'initialisation des notifications:', error);
    }
}

async function requestAndSubscribe() {
    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            throw new Error('Permission refusée');
        }

        const registration = await navigator.serviceWorker.register('/sw.js');
        console.log('Service Worker enregistré:', registration.scope);

        const subscription = await registration.pushManager.getSubscription();
        if (subscription) {
            console.log('Déjà abonné');
            return subscription;
        }

        const newSubscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array('BEhGplSNE_lmI07MuwyIMb5IN53Exd8DPsEqwdLrfjBNhCMrSb87yCHZ5E7cHtIwMrpvFhoWZXsf5zUb2xZ5dXs')
        });

        await saveSubscription(newSubscription);
        console.log('Abonnement sauvegardé');

        // Cacher le bouton après l'abonnement sur mobile
        const btn = document.getElementById('notificationSubscribeBtn');
        if (btn) btn.style.display = 'none';

        return newSubscription;
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

async function saveSubscription(subscription) {
    const response = await fetch('/save-subscription', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(subscription)
    });

    if (!response.ok) {
        throw new Error('Échec de la sauvegarde de l\'abonnement');
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return new Uint8Array([...rawData].map(char => char.charCodeAt(0)));
}

// Initialiser les notifications quand le DOM est chargé
document.addEventListener('DOMContentLoaded', initializeNotifications);