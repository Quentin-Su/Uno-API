<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\UserGame;
use App\Repository\GameRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GameController extends AbstractController
{
    /**
     * @var User $currentUser
     */
    private $currentUser;

    /**
     * @var Game|false $currentGame
     */
    private $currentGame;

    /**
     * @var UserGame|null $currentUserGame
     */
    private $currentUserGame;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->currentUser = $tokenStorage->getToken()->getUser();
        $this->currentGame = $this->currentUser->getGames()->filter(fn ($game) => !in_array($game->getStatus(), ['quit', 'end']))->first();
        $this->currentUserGame = $this->currentGame->getUserGames()->filter(fn ($elem) => $elem->getUserId()->getId() === $this->currentUser->getId())->first();
    }

    #[Route('api/game', name: 'app_game.createGame', methods: ['POST'])]
    #[OA\Tag(name: 'Game')]
    #[Security(name: 'Bearer')]
    public function createGame(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        if ($this->currentGame) return new JsonResponse(['message' => 'You cannot create a new game while you are already in an existing game.'], JsonResponse::HTTP_FORBIDDEN , []);
        
        $game = new Game();
        $userGame = new UserGame();

        $game->setCode(substr(sha1(time() . random_bytes(5)), 0, 6));
        $game->setCreatorId($this->getUser());
        $game->setCreatedAt(new DateTimeImmutable());
        $game->setStatus('waiting');

        $entityManager->persist($game);

        $userGame->setUserId($this->getUser());
        $userGame->setGameId($game);
        $userGame->setStatus('on');
        $userGame->setJoinedAt(new DateTimeImmutable());

        $entityManager->persist($userGame);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(['createGame']);
        $jsonGame = $serializer->serialize($game, 'json', $context);

        return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
    }

    #[Route('api/game/join/{code}', name: 'app_game.joinGame', methods: ['POST'])]
    #[OA\Tag(name: 'Game')]
    #[Security(name: 'Bearer')]
    public function joinGame(string $code, GameRepository $repository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        if ($this->currentGame) return new JsonResponse(['message' => 'You cannot join a new game while you are already in an existing game.'], JsonResponse::HTTP_FORBIDDEN , []);

        $game = $repository->findOneBy(['code' => $code, 'status' => 'waiting']);

        if ($game) {
            if (!$this->currentUserGame) {
                $userGame = new UserGame();

                $userGame->setUserId($this->getUser());
                $userGame->setGameId($game);
                $userGame->setStatus('on');
                $userGame->setJoinedAt(new DateTimeImmutable());

                $entityManager->persist($userGame);

                $game->addUserGame($userGame);

                $entityManager->persist($game);
                $entityManager->flush();

                $context = SerializationContext::create()->setGroups(['joinGame']);
                $jsonGame = $serializer->serialize($game, 'json', $context);
        
                return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
            }
            else return new JsonResponse(['message' => 'You are already in this game.'], JsonResponse::HTTP_NOT_MODIFIED , []);
        }
        else return new JsonResponse(['message' => 'No game found with this code.'], JsonResponse::HTTP_NOT_MODIFIED , []);
    }

    #[Route('api/game/start', name: 'app_game.startGame', methods: ['POST'])]
    #[OA\Tag(name: 'Game')]
    #[Security(name: 'Bearer')]
    public function startGame(EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->currentGame->getCreatorId()->getId() === $this->currentUser->getId()) {
            $this->currentGame->setUpdateAt(new DateTimeImmutable());
            $this->currentGame->setStatus('started');

            $entityManager->persist($this->currentGame);
            $entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
        };

        return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED, []);
    }

    #[Route('api/game/leave', name: 'app_game.leaveGame', methods: ['POST'])]
    #[OA\Tag(name: 'Game')]
    #[Security(name: 'Bearer')]
    public function leaveGame(EntityManagerInterface $entityManager): JsonResponse
    {
        dd($this->currentGame);

        // if (!$this->currentGame) return new JsonResponse(['message' => 'You cannot leave a game you are not currently present in.'], JsonResponse::HTTP_NOT_MODIFIED , []);

        // if ($this->currentGame->getCreatorId()->getId() === $this->currentUser->getId()) {
        //     foreach ($this->currentGame->getUserGames() as $key => $value) {
        //         $value->setStatus('quit');
        //         $entityManager->persist($value);
        //     };

        //     $this->currentGame->setStatus('stop');
        //     $entityManager->persist($this->currentGame);
        // }
        // else {
        //     $this->currentUserGame->setStatus('quit');
        //     $entityManager->persist($this->currentUserGame);
        // };
        // $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}