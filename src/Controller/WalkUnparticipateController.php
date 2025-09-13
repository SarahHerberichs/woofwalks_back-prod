<?php
namespace App\Controller;

use App\Entity\Walk;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Security;

#[AsController]
class WalkUnparticipateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function __invoke(Walk $walk): Walk
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté');
        }

        if ($walk->getParticipants()->contains($user)) {
            $walk->removeParticipant($user);
            $this->em->flush();
        }

        return $walk;
    }
}
