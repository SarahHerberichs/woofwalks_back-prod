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


class PhotoUploadController extends AbstractController
{

    #[Route('/api/main_photo', name: 'upload_photo', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function uploadPhoto(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $photoFile = $request->files->get('file');

        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/media';

            if (!is_dir($uploadDir)) {
                return new JsonResponse(['error' => 'Le dossier de destination n\'existe pas'], 500);
            }

            if (!is_writable($uploadDir)) {
                return new JsonResponse(['error' => 'Le dossier de destination n\'est pas accessible en écriture'], 500);
            }

            try {
                $photoFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/media',
                    $newFilename
                );
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Failed to upload file'], 500);
            }

            $photo = new MainPhoto();
            $photo->setFilePath($newFilename);

            $entityManager->persist($photo);
            $entityManager->flush();

            return new JsonResponse(['id' => $photo->getId(), 'filePath' => '/media/' . $newFilename], 201);
        }

        return new JsonResponse(['error' => 'No file zpeofpzoek'], 400);
    }
}