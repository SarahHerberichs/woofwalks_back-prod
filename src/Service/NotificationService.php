<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Service\SmsService;
use App\Service\EmailService;
use App\Service\MessageBuilder;
use App\Entity\User;
use App\Entity\Notification;
use App\Entity\NotificationType;



class NotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBuilder $messageBuilder,
        private EmailService $emailService,
        private SmsService $smsService
    ) {}

    public function createAndDispatchNotification(User $user, string $typeCode, array $context): void
    {
        $type = $this->em->getRepository(NotificationType::class)->findOneBy(['code' => $typeCode]);

        if (!$type) {
            throw new \LogicException("NotificationType $typeCode not found");
        }

        foreach ($user->getChannelUsers() as $channelUser) {
            $notif = new Notification();
            $notif->setChannelUser($channelUser);
            //Cherche dans la table EventNotificationType l'id correspondant (par exemple walk_update correspond à 3 dans table EventNotifType)
            $notif->setType($type);
            $notif->setCreatedAt(new \DateTime());
            $notif->setContext($context); 

            $this->em->persist($notif);
            $this->sendNotification($notif);
        }

        $this->em->flush();
    }

    private function sendNotification(Notification $notification): void
    {
        $channel = $notification->getChannelUser()->getChannel()->getName();
        $user = $notification->getChannelUser()->getUser();

        $message = $this->messageBuilder->build($notification);

        if ($channel === 'email') {
            $this->emailService->send($user->getEmail(), $notification->getType()->getSubjectEmail(), $message);
        } elseif ($channel === 'sms') {
            $this->smsService->send($user->getPhoneNumber(), $message);
        }
    }
}