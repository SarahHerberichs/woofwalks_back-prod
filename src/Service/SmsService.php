<?php
namespace App\Service;
use App\Service\Interface\NotifierInterface;

class SmsService implements NotifierInterface
{
    public function send(string $to, string $subjectOrMessage, ?string $message = null): void
    {
        echo('SMS SERVICE ACTIF');
    }
}