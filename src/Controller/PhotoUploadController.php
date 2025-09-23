<?php

namespace App\Controller;

use App\Entity\MainPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\PhotoUploadService;

class PhotoUploadController extends AbstractController {
    public function __construct(private PhotoUploadService $uploadService) {}

    #[Route('/api/main_photo', methods: ['POST'])]
    public function uploadMainPhoto(Request $request, EntityManagerInterface $em): JsonResponse {
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'No file provided'], 400);
        }

        try {
            $newFilename = $this->uploadService->upload($file);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        $photo = new MainPhoto();
        $photo->setFilePath($newFilename);

        $em->persist($photo);
        $em->flush();

        return new JsonResponse([
            'id' => $photo->getId(),
            'filePath' => '/uploads/'.$newFilename
        ], 201);
    }
}
