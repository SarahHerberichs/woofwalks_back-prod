<?php

//login_check est géré nativement par lexik qui va vérifier les identifiants et générer un token JWT 
//Il crée un tableau avec ce token sous la clé 'token'
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route; // Très important !
//Controlleur qui ne sert qu'à déclarer la route
class AuthController extends AbstractController
{
    /**
     *[Route("/api/login_check", name: "api_login_check", methods: ["POST"])]
     *
     * Cette méthode est UNIQUEMENT une DÉCLARATION de route pour Symfony.
     * La logique d'authentification pour cette route est entièrement gérée par le bundle Lexik JWT Authentication.
     */
    public function loginCheck(): JsonResponse
    {

        throw new \LogicException('Cette action est gérée par le firewall Lexik JWT Authentication.');
    }

}