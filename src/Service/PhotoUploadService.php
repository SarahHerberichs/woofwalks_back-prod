<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class PhotoUploadService {
    private string $uploadsDirectory;
    
    public function __construct(
        ParameterBagInterface $params,
        private SluggerInterface $slugger
    ) {
        $this->uploadsDirectory = $params->get('uploads_directory');
        $this->slugger = $slugger;
    }

  public function upload(UploadedFile $file): string {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        if (!is_dir($this->uploadsDirectory)) {
            throw new \RuntimeException("Le dossier de destination n'existe pas");
        }

        if (!is_writable($this->uploadsDirectory)) {
            throw new \RuntimeException("Le dossier de destination n'est pas accessible en écriture");
        }

        $file->move($this->uploadsDirectory, $newFilename);

        return $newFilename;
    }
}
