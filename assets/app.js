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

    try {
        // Sur mobile, on attend un clic n'importe où sur la page
        if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            let notificationRequested = false;

            document.addEventListener('click', async () => {
                if (!notificationRequested) {
                    notificationRequested = true;
                    await requestAndSubscribe();
                }
            }, { once: true }); // L'événement ne sera déclenché qu'une seule fois
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
        console.log('1. Début de requestAndSubscribe');

        const permission = await Notification.requestPermission();
        console.log('2. Permission demandée:', permission);

        if (permission !== 'granted') {
            throw new Error('Permission refusée');
        }
        console.log('3. Permission accordée');

        // Enregistrer le Service Worker
        console.log('4. Tentative d\'enregistrement du Service Worker...');
        const registration = await navigator.serviceWorker.register('/sw.js', {
            scope: '/'
        });
        console.log('5. Service Worker enregistré:', registration.scope);

        // Attendre l'activation du Service Worker de manière explicite
        if (registration.installing || registration.waiting) {
            await new Promise((resolve, reject) => {
                const serviceWorker = registration.installing || registration.waiting;
                serviceWorker.addEventListener('statechange', function () {
                    if (this.state === 'activated') {
                        console.log('Service Worker activé avec succès');
                        resolve();
                    } else if (this.state === 'redundant') {
                        reject(new Error('Service Worker devenu redondant'));
                    }
                });
            });
        }

        console.log('7. Service Worker prêt');

        // Vérifier l'abonnement existant
        const subscription = await registration.pushManager.getSubscription();
        if (subscription) {
            console.log('8. Abonnement existant trouvé');
            return subscription;
        }

        console.log('9. Création d\'un nouvel abonnement...');
        const newSubscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array('BEhGplSNE_lmI07MuwyIMb5IN53Exd8DPsEqwdLrfjBNhCMrSb87yCHZ5E7cHtIwMrpvFhoWZXsf5zUb2xZ5dXs')
        });

        console.log('10. Sauvegarde de l\'abonnement...');
        await saveSubscription(newSubscription);

        return newSubscription;
    } catch (error) {
        console.error('Erreur détaillée:', error);
        throw error;
    }
}

async function saveSubscription(subscription) {
    try {
        console.log('Données envoyées au serveur:', JSON.stringify(subscription));
        const response = await fetch('/save-subscription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(subscription)
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Échec de la sauvegarde de l'abonnement: ${response.status} ${errorText}`);
        }

        const responseData = await response.json();
        console.log('Réponse du serveur:', responseData);
    } catch (error) {
        console.error('Erreur lors de la sauvegarde:', error);
        throw error;
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