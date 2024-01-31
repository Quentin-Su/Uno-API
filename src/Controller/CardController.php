<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CardController extends AbstractController
{
    #[Route('api/card', name: 'app_card.getAll', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns an array of all cards present in the database',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Card::class, groups: ['getAllCard']))
        )
    )]
    #[OA\Tag(name: 'Card')]
    #[Security(name: 'Bearer')]
    public function getAllCard(CardRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = 'getAllCards';
        $jsonCards = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer) {
            $item->tag('cardsCache');

            $cardsList = $repository->findAll();
            $context = SerializationContext::create()->setGroups(['getAllCard']);

            return $serializer->serialize($cardsList, 'json', $context);
        });

        return new JsonResponse($jsonCards, Response::HTTP_OK, [], true);
    }

    #[Route('api/card/{id}', name: 'app_card.getById', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the card who owns the Id',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Card::class, groups: ['getAllCard']))
        )
    )]
    #[OA\Tag(name: 'Card')]
    #[Security(name: 'Bearer')]
    public function getCard(int $id, CardRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $card = $repository->find($id);

        $context = SerializationContext::create()->setGroups(['getAllCard']);
        $jsonCard = $serializer->serialize($card, 'json', $context);

        return new JsonResponse($jsonCard, Response::HTTP_OK, [], true);
    }

    // #[Route('api/card/{id}', name: 'app_card.updateCard', methods: ['PUT', 'PATCH'])]
    // #[OA\RequestBody(
    //     description: 'Update Card fields',
    //     content: new OA\MediaType(
    //         mediaType: 'application/json',
    //         schema: new OA\Schema(ref: new Model(type: Card::class, groups: ['updateCard']))
    //     )
    // )]
    // #[OA\Tag(name: 'Card')]
    // #[Security(name: 'Bearer')]
    // #[IsGranted('ROLE_ADMIN', message:'Unauthorized')]
    // public function updateCard(int $id, Request $request, CardRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    // {       
    //     $card = $repository->find($id);

    //     $updateCard = $serializer->deserialize($request->getContent(), Card::class, 'json');
    //     $card->setImage($updateCard->getImage() ?? $card->getImage());

    //     return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    // }
}