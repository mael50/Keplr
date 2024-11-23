<?php

namespace App\Controller;

use DateTime;
use App\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PushController extends AbstractController
{
    #[Route('/save-subscription', name: 'save_subscription', methods: ['POST'])]
    public function saveSubscription(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier l'authentification
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (
            !isset($data['endpoint']) ||
            !isset($data['keys']['p256dh']) ||
            !isset($data['keys']['auth'])
        ) {
            return new JsonResponse(['error' => 'Données de souscription invalides'], 400);
        }

        try {
            // Vérifier si un abonnement existe déjà avec ces clés
            $existingSubscription = $entityManager->getRepository(PushSubscription::class)->findOneBy([
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
                'user' => $user
            ]);

            if ($existingSubscription) {
                // Mettre à jour l'endpoint si nécessaire
                $existingSubscription->setEndpoint($data['endpoint']);
                $existingSubscription->setExpirationTime($data['expirationTime'] ?? null);

                $entityManager->flush();
                return new JsonResponse(['success' => true, 'updated' => true], 200);
            }

            // Créer un nouvel abonnement si aucun n'existe
            $pushSubscription = new PushSubscription();
            $pushSubscription
                ->setEndpoint($data['endpoint'])
                ->setExpirationTime($data['expirationTime'] ?? null)
                ->setP256dh($data['keys']['p256dh'])
                ->setAuth($data['keys']['auth'])
                ->setUser($user)
                ->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($pushSubscription);
            $entityManager->flush();

            return new JsonResponse(['success' => true, 'created' => true], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement'], 500);
        }
    }
}
