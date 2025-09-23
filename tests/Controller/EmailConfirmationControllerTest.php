<?php

namespace App\Tests\Controller;

use App\Controller\EmailConfirmationController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailConfirmationControllerTest extends TestCase {

    public function testConfirmEmailWithInvalidToken() {

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')
            ->with(['confirmationToken' => 'invalid-token'])
            ->willReturn(null);

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

    public function testConfirmEmailAlreadyVerified() {

        $user = $this->createMock(User::class);
        //Déclare que l'user est déjà vérifié
        $user->method('isVerified')->willReturn(true);

        $userRepository = $this->createMock(UserRepository::class);
        //Simule un repo dont findoneby retourne l'user cree ci dessus
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

    public function testConfirmEmailSuccess() {
    
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['isVerified', 'setConfirmationToken', 'setIsVerified', 'getConfirmationRequestedAt'])
            //Rendre le Mock utilisable 
            ->getMock();
        //Force l'user à Non Vérifié et simule une date de confirmation de compte
        $user->method('isVerified')->willReturn(false);
        $user->method('getConfirmationRequestedAt')->willReturn(new \DateTimeImmutable()); 
        //Methode de supression du token apellé une fois pour le nuller, methode is verify passe status à true une fois
        $user->expects($this->once())->method('setConfirmationToken')->with(null);
        $user->expects($this->once())->method('setIsVerified')->with(true);

        $userRepository = $this->createMock(UserRepository::class);
        //Associe la méthode du repository  a retourner l'user crée 
        $userRepository->method('findOneBy')
            ->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $controller = new EmailConfirmationController();
        //Apres avoir associé l'utilisateur a un ustilisateur vérifié - on envoi ca au controlleur
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
