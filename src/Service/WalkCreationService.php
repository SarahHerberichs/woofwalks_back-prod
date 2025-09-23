<?php

namespace App\Service;
use App\Entity\Chat;
use App\Entity\User;
use App\Entity\Walk;
use App\Entity\Photo;
use App\Repository\LocationRepository;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\WalkValidationService;

class WalkCreationService {
    private EntityManagerInterface $entityManager;
    private PhotoRepository $photoRepository;
    private LocationRepository $locationRepository;
    private Security $security;
    private WalkValidationService $validationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PhotoRepository $photoRepository,
        LocationRepository $locationRepository,
        Security $security,
        WalkValidationService $validationService) {

        $this->entityManager = $entityManager;
        $this->photoRepository = $photoRepository;
        $this->locationRepository = $locationRepository;
        $this->security = $security;
        $this->validationService = $validationService;
    }

    public function createWalk(array $data): ?Walk {
        // Validation de la Data par le Service validation
        $validationResult = $this->validationService->validateWalkData($data);
        if (!$validationResult->isValid()) {
            return null;
        }

        try {
            $datetime = new \DateTime($data['datetime']);
        } catch (\Exception $e) {
            return null; 
        }       
        
        $photo = $this->photoRepository->find($data['photo']);
        if (!$photo) {
            return null; 
        }
   
        $location = $this->locationRepository->find($data['location']);
        $isCustomLocation = isset($data['is_custom_location']) && $data['is_custom_location'] === true;
        $creator = $this->security->getUser();
  
        if (!$creator instanceof User) {
            return null; 
        }

        $walk = new Walk();
        $walk->setTitle($data['title']);
        $walk->setDescription($data['description']);
        $walk->setMainPhoto($photo);
        $walk->setDate($datetime);
        $walk->setMaxParticipants($data['max_participants'] ?? 0); 
        $walk->setLocation($location);
        $walk->setCreator($creator);
        $walk->setIsCustomLocation($isCustomLocation);
        $walk->addParticipant($creator);
        
        $this->entityManager->persist($walk);
        $this->entityManager->flush();

        return $walk;
    }
}

