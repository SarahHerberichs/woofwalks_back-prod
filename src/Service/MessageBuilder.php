<?php

namespace App\Service;
use App\Entity\Notification;
class MessageBuilder
{
    public function build(Notification $notification): string
    {
        $type = $notification->getType()->getCode();
        $context = $notification->getContext(); // tableau associatif

        return match ($type) {
            'walk_slot_available' => "Une place s’est libérée pour la walk « " . $context['walk']->getTitle() . " ». Tu peux maintenant t’inscrire si tu es rapide !",
            'walk_update'  => "La walk « " . $context['walk']->getTitle() . " » a été modifiée.",
            'new_feedback' => "Un nouveau feedback a été posté sur la walk « " . $context['walk']->getTitle() . " ».",
            'new_message'  => "Nouveau message de " . $context['sender']->getUsername() . " dans la walk « " . $context['walk']->getTitle() . " ».",
            default        => "Vous avez une nouvelle notification.",
        };
    }
}