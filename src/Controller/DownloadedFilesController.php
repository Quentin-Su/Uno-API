<?php

namespace App\Controller;

use App\Entity\DownloadedFiles;
use App\Repository\DownloadedFilesRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

class DownloadedFilesController extends AbstractController
{
    #[Route('/', name: 'app.index')]
    public function index(): void
    {
    
    }

    #[Route('api/file', name: 'app_files.create', methods: ['POST'])]
    #[OA\Tag(name: 'Image')]
    #[Security(name: 'Bearer')]
    public function postPicture(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $newFile = new DownloadedFiles();
        $file = $request->files->get('file');

        $newFile->setFile($file);
        $entityManager->persist($newFile);
        $entityManager->flush();

        $realname = $newFile->getRealName();
        $realpath = $newFile->getRealPath();
        $slug = $newFile->getSlug();

        $arrayPicture = [
            'id' => $newFile->getId(),
            'name' => $newFile->getName(),
            'realname' => $realname,
            'realpath' => $realpath,
            'minetype' => $newFile->getMineType(),
            'slug' => $slug
        ];

        $location = $urlGenerator->generate('app.index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($arrayPicture, Response::HTTP_CREATED, ['Location' => $location . $realpath . '/' . $slug]);
    }

    #[Route('api/file/{id}', name: 'app_files.update', methods: ['POST'])]
    #[OA\Tag(name: 'Image')]
    #[Security(name: 'Bearer')]
    public function updatePicture(int $id, DownloadedFilesRepository $repository, Request $request, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        // Change status of initial file
        $file = $repository->find($id);
        $file->setUpdateAt(new DateTimeImmutable());
        $file->setStatus('off');
        
        $entityManager->persist($file);

        // Create new File
        $newFile = new DownloadedFiles();
        $requestFile = $request->files->get('file');

        $newFile->setFile($requestFile);
        $entityManager->persist($newFile);

        $cards = $file->getCards();
        foreach ($cards as $key => $card) {
            $newCard = $card->setImage($newFile);
            $entityManager->persist($newCard);
        };

        $cache->invalidateTags(['cardsCache']);

        $entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}
