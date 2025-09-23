<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserSecurityTest extends WebTestCase {
    private $client;
    private $em;

    protected function setUp(): void {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createUser(string $email, array $roles = []): User {
        
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $user->setIsVerified(true);
        $user->setCgvAccepted(true);
        $user->setUsername(explode('@', $email)[0]);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function testAdminCanSeeAllUsers(): void {
        // Création d'un admin et d'utilisateurs normaux
        $admin = $this->createUser('admin_'.uniqid().'@example.com', ['ROLE_ADMIN']);
        $user  = $this->createUser('user_'.uniqid().'@example.com');
        $user  = $this->createUser('user_'.uniqid().'@example.com');

        // Forcer la connexion de l'admin
        $this->client->loginUser($admin);

        // Faire la requête protégée en HTTPS
        $this->client->request(
            'GET',
            '/api/users',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'HTTPS' => true]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testRegularUserCannotSeeAllUsers(): void {

        $user  = $this->createUser('user_'.uniqid().'@example.com');

        $this->client->loginUser($user);

           $this->client->request(
        'GET',
        '/api/users',
        [],
        [],
        ['HTTP_ACCEPT' => 'application/json', 'HTTPS' => true]
    );
        // Accès refusé pour un user normal
        $this->assertResponseStatusCodeSame(403); 
    }

    public function testUserCanSeeOwnProfile(): void {

        $user  = $this->createUser('user_'.uniqid().'@example.com');

        $this->client->loginUser($user);

         $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');

        $token = $jwtManager->create($user);

        $this->client->request(
            'GET',
            '/api/users/' . $user->getId(),
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTPS' => true,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ]
        );
            $this->assertResponseIsSuccessful();
    }

    public function testUserCannotSeeOtherProfile(): void {

        $user1  = $this->createUser('user_'.uniqid().'@example.com');
        $user2  = $this->createUser('user_'.uniqid().'@example.com');

        $this->client->loginUser($user1);

        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');

        $token = $jwtManager->create($user1);

        $this->client->request(
            'GET',
            '/api/users/' . $user2->getId(),
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTPS' => true,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token 
            ]
        );
        $this->assertResponseStatusCodeSame(403);
    }
}
