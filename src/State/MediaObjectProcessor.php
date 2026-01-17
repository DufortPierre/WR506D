<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MediaObject;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

final class MediaObjectProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private KernelInterface $kernel,
        private RequestStack $requestStack
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof MediaObject) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $request = $this->requestStack->getCurrentRequest();
        
        // Handle file upload from multipart/form-data
        if ($request && $request->files->has('file')) {
            $file = $request->files->get('file');
            
            if ($file instanceof UploadedFile) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate(
                    'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                    $originalFilename
                );
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                $uploadsDir = $this->kernel->getProjectDir() . '/public/uploads';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }

                try {
                    $file->move($uploadsDir, $newFilename);
                    $data->setContentUrl('/uploads/' . $newFilename);
                } catch (FileException $e) {
                    throw new \RuntimeException('Failed to upload file: ' . $e->getMessage());
                }
            }
        } elseif ($request && $request->request->has('contentUrl')) {
            // Handle direct URL (for testing or external URLs)
            $data->setContentUrl($request->request->get('contentUrl'));
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
