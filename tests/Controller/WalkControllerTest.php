<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class WalkControllerTest extends WebTestCase
{
    public function testCreateWalkSuccess()
    {
        $client = static::createClient();
        $container = static::getContainer();

        // Récupérer un utilisateur test
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy([]);
        $this->assertNotNull($user, 'Pas d’utilisateur trouvé pour le test');

        // Générer un JWT valide
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $data = [
            'title' => 'Ma balade',
            'description' => 'Belle balade en forêt',
            'datetime' => '2025-08-16T10:00:00',
            'photo' => 1,
            'location' => 1,
            'is_custom_location' => true,
            'max_participants' => 10,
        ];
        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie(
            'BEARER',
            $token,
            time() + 3600,
            '/',
            '',
            false,
            true,
            false,
            'Lax'
        ));

        $client->request(
            'POST',
            '/api/walkscustom',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('Walk created successfully', $response->getContent());
    }
}
