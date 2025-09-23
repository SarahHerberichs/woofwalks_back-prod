<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\MainPhoto;
use App\Entity\Location;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class WalkControllerFunctionalTest extends WebTestCase {
    /**
     * Helper method pour simuler un login et récupérer les cookies CSRF
     */
    private function loginAndGetCsrfToken($client, $user): string {
        // Simule un login pour obtenir les cookies CSRF
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $user->getEmail(),
            'password' => 'password' 
        ]));
        
        $response = $client->getResponse();

        // Récupère le cookie XSRF-TOKEN
        $cookies = $response->headers->getCookies();
      
        //Test voir si les 3 cookies sont récupérés
        foreach ($cookies as $cookie) {
            echo "Cookie: " . $cookie->getName() . " = " . $cookie->getValue() . "\n";
        }

        $xsrfToken = null;
        //cherche ds les cookies le Xsrf et le stocke 
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                $xsrfToken = $cookie->getValue();
                break;
            }
        }
        //Vérifie que xsrf pas nul, sinnon echec avec msg erreur
        $this->assertNotNull($xsrfToken, 'Cookie XSRF-TOKEN manquant après login');
        
        return $xsrfToken;
    }
    
    /**
     * Helper method pour récupérer le cookie CSRF depuis les cookies de la requête
     */
    private function getCsrfTokenFromCookies($client): string {
        // Récupère les cookies de la session
        $cookies = $client->getCookieJar()->all();
        $xsrfToken = null;
        
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                $xsrfToken = $cookie->getValue();
                break;
            }
        }
        
        $this->assertNotNull($xsrfToken, 'Cookie XSRF-TOKEN manquant dans la session');
        
        return $xsrfToken;
    }

    
    public function testCreateWalkEndpoint() {
        // 1. Crée un navigateur 
        $client = static::createClient();
        // Désactivation de gestion des exceptions de Symfony pour qu'elles soient levées directement par PHPUnit
        $client->catchExceptions(false);
        // Récupère le conteneur de services de l'application (Doctrine, Security,Router,Services,Controllers...)
        $container = static::getContainer();

        // 2. Préparation des données d'authentification
        // Récupère éléments nécessaires en BDD
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
        $photo = $container->get('doctrine')->getRepository(MainPhoto::class)->findOneBy([]);
        $location = $container->get('doctrine')->getRepository(Location::class)->findOneBy([]);
        // Vérifie que les données existent bien
        $this->assertNotNull($user, 'Aucun utilisateur trouvé pour le test');
        $this->assertNotNull($photo, 'Aucune photo trouvée dans les fixtures.');
        $this->assertNotNull($location, 'Aucune localisation trouvée dans les fixtures.');

        // Récupère le service de gestion des tokens JWT pour en générer un.
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        // Crée le token pour l'utilisateurtest.
        $token = $jwtManager->create($user);
        // S'assure que le token n'est pas vide.
        $this->assertNotEmpty($token, 'Le token JWT est vide');

        // 3. Récupération du token CSRF via login méthode
        $csrfToken = $this->loginAndGetCsrfToken($client, $user);

        // 4. Préparation des données de la requête
        // Le tableau de données que nous allons envoyer dans la requête POST.
        $data = [
            'title' => 'Balade test',
            'description' => 'Description de la balade de test',
            'datetime' => '2025-08-16T10:00:00', 
            'photo' => 1, 
            'location' => 1, 
            'is_custom_location' => true,
            'max_participants' => 10, 
        ];

        // 5. Envoi de la requête HTTP
        // Lance une requête POST vers l'URL de création de balade. Avec Data et Tokens
        $client->request(
            'POST',
            '/api/walkscustom',
            [], // Paramètres de requête
            [], // Fichiers
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token, 
                'HTTP_X_CSRF_TOKEN' => $csrfToken, 
                'CONTENT_TYPE' => 'application/json' 
            ],
            json_encode($data)
        );

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        // Si champ 'errors' dans la réponse
        if (isset($responseData['errors'])) {
            echo "Erreurs de validation: \n" . json_encode($responseData['errors'], JSON_PRETTY_PRINT) . "\n";
        }

        // 6.Vérif que rép 201 et corps réponse = msg succes attendu
        $this->assertEquals(201, $response->getStatusCode(), 'Status code incorrect');
        $this->assertStringContainsString('Walk created successfully', $response->getContent(), 'Message attendu non trouvé');
    }

    public function testCreateWalkWithAuthenticatedUser() {

        $client = static::createClient();
        $client->catchExceptions(false);
        $container = static::getContainer();

        // Récupère un utilisateur existant
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
        $this->assertNotNull($user, 'Aucun utilisateur trouvé pour le test');

        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        // Récupère un token CSRF via login
        $csrfToken = $this->loginAndGetCsrfToken($client, $user);

        // Prépare les données de la requête
        $data = [
            'title' => 'Balade test',
            'description' => 'Description de la balade de test',
            'datetime' => '2025-08-16T10:00:00',
            'photo' => 1,       // ID d'une photo existante en BDD
            'location' => 1,    // ID d'une location existante en BDD
            'is_custom_location' => true,
            'max_participants' => 10,
        ];

        // Envoie la requête avec JWT et CSRF
        $client->request(
            'POST',
            '/api/walkscustom',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_X_CSRF_TOKEN' => $csrfToken,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data)
        );

        $response = $client->getResponse();

        // Vérifications
        $this->assertEquals(201, $response->getStatusCode(), 'Le token valide doit permettre la création de la balade');
        $this->assertStringContainsString('Walk created successfully', $response->getContent());
    }

    public function testCreateWalkWithUnauthenticatedUser() {

        $client = static::createClient();
        $client->catchExceptions(false);

        // Prépare les données de la requête
        $data = [
            'title' => 'Balade test',
            'description' => 'Description de la balade de test',
            'datetime' => '2025-08-16T10:00:00',
            'photo' => 1,
            'location' => 1,
            'is_custom_location' => true,
            'max_participants' => 10,
        ];

        // Envoie la requête sans JWT et sans CSRF 
        $client->request(
            'POST',
            '/api/walkscustom',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $client->getResponse();

        // Vérifications - Doit échouer sur CSRF (403) car pas de token CSRF
        $this->assertEquals(403, $response->getStatusCode(), 'Sans CSRF, doit recevoir 403');
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('CSRF token invalide ou manquant', $responseData['message']);
    }


    public function testCreateWalkWithJwtButNoCsrf() {

        $client = static::createClient();
        $client->catchExceptions(false);
        $container = static::getContainer();

        // Récupère un utilisateur existant
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
        $this->assertNotNull($user, 'Aucun utilisateur trouvé pour le test');

        // Crée un token JWT valide
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        // Prépare les données de la requête
        $data = [
            'title' => 'Balade test',
            'description' => 'Description de la balade de test',
            'datetime' => '2025-08-16T10:00:00',
            'photo' => 1,
            'location' => 1,
            'is_custom_location' => true,
            'max_participants' => 10,
        ];

        // Envoie la requête avec JWT mais **sans CSRF**
        $client->request(
            'POST',
            '/api/walkscustom',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data)
        );

        $response = $client->getResponse();

        // Vérifications - Doit échouer sur CSRF (403)
        $this->assertEquals(403, $response->getStatusCode(), 'Avec JWT mais sans CSRF, doit recevoir 403');
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('CSRF token invalide ou manquant', $responseData['message']);
    }

    //Vérifie que l'appli bloque si pas de csrf , meme si un token est présent (quil soit valide ou non : CSRF en premier : 403 (pas autorisé) et non 401 (probleme jwt))
    public function testCreateWalkWithInvalidJwt() {

        $client = static::createClient();
        $client->catchExceptions(false);

        // Prépare les données de la requête
        $data = [
            'title' => 'Balade test',
            'description' => 'Description de la balade de test',
            'datetime' => '2025-08-16T10:00:00',
            'photo' => 1,
            'location' => 1,
            'is_custom_location' => true,
            'max_participants' => 10,
        ];

        // Envoie la requête avec JWT invalide
        $client->request(
            'POST',
            '/api/walkscustom',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer invalid_token',
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data)
        );

        $response = $client->getResponse();

        // Vérifications - Doit échouer sur CSRF (403) car CSRF a priorité sur JWT -- Si c'était le bearer invalide qui posait probleme : 401
        $this->assertEquals(403, $response->getStatusCode(), 'Avec JWT invalide mais sans CSRF, doit recevoir 403');
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('CSRF token invalide ou manquant', $responseData['message']);
    }


    public function testCreateWalkWithValidCsrfButInvalidJwt() {

        $client = static::createClient();
        $client->catchExceptions(false);
        $container = static::getContainer();

        // Récupère un utilisateur 
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
        $this->assertNotNull($user, 'Aucun utilisateur trouvé pour le test');

        // Récupère un token CSRF via login
        $csrfToken = $this->loginAndGetCsrfToken($client, $user);

        // Prépare les données de la requête
        $data = [
            'title' => 'Balade test',
            'description' => 'Description de la balade de test',
            'datetime' => '2025-08-16T10:00:00',
            'photo' => 1,
            'location' => 1,
            'is_custom_location' => true,
            'max_participants' => 10,
        ];

        // Envoie la requête avec CSRF valide mais JWT invalide
        $client->request(
            'POST',
            '/api/walkscustom',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer invalid_token',
                'HTTP_X_CSRF_TOKEN' => $csrfToken,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data)
        );

        $response = $client->getResponse();

        // Vérifications - Doit échouer sur JWT (401) car CSRF est valide
        $this->assertEquals(401, $response->getStatusCode(), 'Avec CSRF valide mais JWT invalide, doit recevoir 401');
        
        // Vérifie que la réponse n'est pas vide
        $this->assertNotEmpty($response->getContent(), 'Réponse vide');
        
        // Vérifie que c'est bien une erreur d'authentification
        // Le statut 401 indique déjà une erreur d'authentification
        $this->assertEquals(401, $response->getStatusCode(), 'Doit recevoir 401 pour JWT invalide');
    }

}