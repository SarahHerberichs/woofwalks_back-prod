<?php
namespace App\Service;

use App\Service\Interface\NotifierInterface;

class EmailService implements NotifierInterface
{
    public function send(string $to, string $subjectOrMessage, ?string $message = null): void
    {
        echo('SERVICE EMAIL OK');

    }
}