<?php

namespace App\Service\Contract;

interface NotifierInterface
{
    public function send(string $to, string $subjectOrMessage, ?string $message = null): void;
}