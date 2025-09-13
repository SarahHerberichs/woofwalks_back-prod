<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository; // Importe le UserRepository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserRegistrationSubscriber implements EventSubscriberInterface
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
// va se déclencher apres désérialisation mais avant valiation et persistance
    public static function getSubscribedEvents(): array {
        return [
            KernelEvents::VIEW => ['onPreRegisterUser', 200],                       
        ];
    }

    public function onPreRegisterUser(ViewEvent $event): void {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // On s'intéresse uniquement aux requêtes POST sur l'entité User (pour l'inscription)
        if (!$user instanceof User || $method !== 'POST') {
            return;
        }
        // Récupère l'e-mail du nouvel utilisateur qui tente de s'inscrire
        $email = $user->getEmail();
        if (empty($email)) {
            return;
        }
        // Recherche un utilisateur existant avec cet e-mail
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            $expirationTime = (new \DateTimeImmutable())->modify('-24 hours');
            //Si l'utilisateur est en BDD mais n'a pas validé son compte meme s'il a recu son mail - on supprime
            if (!$existingUser->isVerified() && 
                $existingUser->getConfirmationRequestedAt() !== null && 
                $existingUser->getConfirmationRequestedAt() < $expirationTime) {
                
                $this->entityManager->remove($existingUser);
                $this->entityManager->flush(); 
                return; 
            }

            $event->setResponse(new JsonResponse([
                'detail' => 'Cet email est déjà utilisé pour un autre compte.'
            ], 400));
            return;
        }
    }
}