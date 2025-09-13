<?php

use Symfony\Component\Dotenv\Dotenv;

// Charge l’autoload de Composer
require dirname(__DIR__).'/vendor/autoload.php';

// Charge le .env uniquement s'il existe
if (class_exists(Dotenv::class) && file_exists(dirname(__DIR__).'/.env.railway')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.railway');
}
