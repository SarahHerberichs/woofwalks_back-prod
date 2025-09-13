<?php
namespace App\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Entity\Walk;
use App\Entity\WalkAlertRequest;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;


class WalkUpdateListener
{
    // public function __construct(
    //     private NotificationService $notificationService,
    //     private EntityManagerInterface $em
    // ) {}
   public function postUpdate(Walk $walk, LifecycleEventArgs $args): void
    {
        // juste pour voir si le listener est déclenché
    }
    // public function postUpdate(Walk $walk, LifecycleEventArgs $args): void {
    //     // Notifier les participants d'une modification de la walk
    //     foreach ($walk->getParticipants() as $user) {
    //         $this->notificationService->createAndDispatchNotification(
    //             $user,
    //             'walk_update',
    //             ['walk' => $walk]
    //         );
    //     }

    //     // Vérifier si une place s’est libérée
    //     if (count($walk->getParticipants()) < $walk->getMaxParticipants()) {
    //         $alertRequests = $this->em->getRepository(WalkAlertRequest::class)
    //             ->findBy(['walk' => $walk, 'notified' => false]);

    //         foreach ($alertRequests as $request) {
    //             $this->notificationService->createAndDispatchNotification(
    //                 $request->getUser(),
    //                 'walk_slot_available',
    //                 ['walk' => $walk]
    //             );

    //             $request->setNotified(true);
    //             $this->em->persist($request);
    //         }
    //         $this->em->flush();
    //     }
    // }
}
