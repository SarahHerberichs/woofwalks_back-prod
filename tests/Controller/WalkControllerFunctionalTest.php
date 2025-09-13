<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\MainPhoto;
use App\Entity\Location;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

// La classe de test étend WebTestCase, ce qui fournit les outils nécessaires pour les tests fonctionnels Symfony
class WalkControllerFunctionalTest extends WebTestCase
{

    // C'est la méthode de test, le nom commence par 'test' pour être détecté par PHPUnit
    public function testCreateWalkEndpoint()
    {
        // 1. Initialisation du client
        // Crée un client HTTP qui simule un navigateur pour interagir avec l'application.
        $client = static::createClient();
        // Désactive la gestion des exceptions de Symfony pour qu'elles soient levées directement par PHPUnit
        $client->catchExceptions(false);
        // Récupère le conteneur de services de l'application
        $container = static::getContainer();

        // 2. Préparation des données d'authentification
        // Utilise la base de données de test pour trouver le premier utilisateur (créé par les fixtures).
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
        // Trouve une Photo et une Location qui existent déjà dans la base de données de test
        $photo = $container->get('doctrine')->getRepository(MainPhoto::class)->findOneBy([]);
        $location = $container->get('doctrine')->getRepository(Location::class)->findOneBy([]);
        // Vérifie que l'utilisateur existe bien pour que le test puisse continuer.
        $this->assertNotNull($user, 'Aucun utilisateur trouvé pour le test');
        $this->assertNotNull($photo, 'Aucune photo trouvée dans les fixtures.');
        $this->assertNotNull($location, 'Aucune localisation trouvée dans les fixtures.');

        // Récupère le service de gestion des tokens JWT pour en générer un.
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        // Crée le token pour l'utilisateur de test.
        $token = $jwtManager->create($user);
        // S'assure que le token n'est pas vide.
        $this->assertNotEmpty($token, 'Le token JWT est vide');

        // 3. Préparation des données de la requête
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

        // 4. Envoi de la requête HTTP
        // Lance une requête POST vers l'URL de création de balade.
        $client->request(
            'POST',
            '/api/walkscustom',
            [], // Paramètres de requête
            [], // Fichiers
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token, // Envoie le token JWT dans l'en-tête d'autorisation standard
                'CONTENT_TYPE' => 'application/json' // Indique que le corps de la requête est en JSON
            ],
            json_encode($data) // Encode les données en JSON pour le corps de la requête
        );

        // Récupère l'objet de réponse HTTP.
            $response = $client->getResponse();
            $responseData = json_decode($response->getContent(), true);

            echo "\nStatus : " . $response->getStatusCode() . "\n";
            echo "Contenu : " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

            // Si vous champ 'errors' dans la réponse, affichez-le
            if (isset($responseData['errors'])) {
                echo "Erreurs de validation: \n" . json_encode($responseData['errors'], JSON_PRETTY_PRINT) . "\n";
            }

            $this->assertEquals(201, $response->getStatusCode(), 'Status code incorrect');

            // 5. Débogage
            echo "\nStatus : " . $response->getStatusCode() . "\n";
            echo "Contenu : " . $response->getContent() . "\n";

        // 6. Assertions (Vérifications)
        // Vérifie que le code de statut de la réponse est bien 201 (Créé).
        $this->assertEquals(201, $response->getStatusCode(), 'Status code incorrect');
        // Vérifie que le corps de la réponse contient le message de succès attendu.
        $this->assertStringContainsString('Walk created successfully', $response->getContent(), 'Message attendu non trouvé');
    }

public function testCreateWalkWithAuthenticatedUser()
{
    $client = static::createClient();
    $client->catchExceptions(false);
    $container = static::getContainer();

    // Récupère un utilisateur existant
    $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
    $this->assertNotNull($user, 'Aucun utilisateur trouvé pour le test');

    // Crée un token JWT valide
    /** @var JWTTokenManagerInterface $jwtManager */
    $jwtManager = $container->get(JWTTokenManagerInterface::class);
    $token = $jwtManager->create($user);

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

    // Envoie la requête avec JWT dans l'en-tête Authorization
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

    // Vérifications
    $this->assertEquals(201, $response->getStatusCode(), 'Le token valide doit permettre la création de la balade');
    $this->assertStringContainsString('Walk created successfully', $response->getContent());
}

public function testCreateWalkWithUnauthenticatedUser()
{
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

    // Envoie la requête **sans JWT**
    $client->request(
        'POST',
        '/api/walkscustom',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode($data)
    );

    $response = $client->getResponse();

    // Vérifications
    $this->assertEquals(401, $response->getStatusCode(), 'Un utilisateur non authentifié doit recevoir un 401');
$responseData = json_decode($response->getContent(), true);
$this->assertEquals('Aucun token JWT dans la requête.', $responseData['error']);}

}