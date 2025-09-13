<?php

namespace App\Tests\Controller;

use App\Controller\EmailConfirmationController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailConfirmationControllerTest extends TestCase
{
    public function testConfirmEmailWithInvalidToken()
    {
        // Mock UserRepository to return null for invalid token
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')
            ->with(['confirmationToken' => 'invalid-token'])
            ->willReturn(null);

        // Mock EntityManager (not used in this case)
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $controller = new EmailConfirmationController();

        $response = $controller->confirmEmail('invalid-token', $userRepository, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'error',
                'message' => 'Lien de confirmation invalide ou déjà utilisé.'
            ]),
            $response->getContent()
        );
    }

    public function testConfirmEmailAlreadyVerified()
    {
        // Create a stub User with isVerified = true
        $user = $this->createMock(User::class);
        $user->method('isVerified')->willReturn(true);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')
            ->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $controller = new EmailConfirmationController();

        $response = $controller->confirmEmail('some-token', $userRepository, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'info',
                'message' => 'Votre adresse email est déjà vérifiée.'
            ]),
            $response->getContent()
        );
    }

    public function testConfirmEmailSuccess()
    {
        // Create a mock User
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['isVerified', 'setConfirmationToken', 'setIsVerified', 'getConfirmationRequestedAt'])
            ->getMock();

        $user->method('isVerified')->willReturn(false);
        $user->method('getConfirmationRequestedAt')->willReturn(new \DateTimeImmutable()); 
        $user->expects($this->once())->method('setConfirmationToken')->with(null);
        $user->expects($this->once())->method('setIsVerified')->with(true);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')
            ->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $controller = new EmailConfirmationController();

        $response = $controller->confirmEmail('valid-token', $userRepository, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'success',
                'message' => 'Adresse email confirmée. Vous pouvez maintenant vous connecter.'
            ]),
            $response->getContent()
        );
    }
}
