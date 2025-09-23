<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class WalkControllerTest extends WebTestCase {
    
    private function loginAndGetCsrfToken($client, $user): string {
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $user->getEmail(),
            'password' => 'password'
        ]));

        $response = $client->getResponse();
        $cookies = $response->headers->getCookies();
        
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                return $cookie->getValue();
            }
        }
        
        $this->fail('Token CSRF non trouvé');
    }

    public function testCreateWalkSuccess() {

        $client = static::createClient();
        $client->catchExceptions(false);
        $container = static::getContainer();

        // Récupérer un utilisateur test
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
        $this->assertNotNull($user, 'Pas d’utilisateur trouvé pour le test');

        // Générer un JWT valide
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $csrfToken = $this->loginAndGetCsrfToken($client, $user);

        $data = [
            'title' => 'Ma balade',
            'description' => 'Belle balade en forêt',
            'datetime' => '2025-08-16T10:00:00',
            'photo' => 1,
            'location' => 1,
            'is_custom_location' => true,
            'max_participants' => 10,
        ];
       $client->request(
            'POST',
            '/api/walkscustom',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,  // ← JWT dans header
                'HTTP_X_CSRF_TOKEN' => $csrfToken,          // ← CSRF dans header
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data)
        );

        $response = $client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('Walk created successfully', $response->getContent());
    }
}
