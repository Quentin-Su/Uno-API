<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\UserCard;
use App\Entity\UserGame;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\UserGameRepository;
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

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->currentUser = $tokenStorage->getToken()->getUser();
    }

    #[Route('api/game', name: 'app_game.createGame', methods: ['POST'])]
    #[OA\Tag(name: 'Game')]
    #[Security(name: 'Bearer')]
    public function createGame(EntityManagerInterface $entityManager, SerializerInterface $serializer, GameRepository $gameRepository): JsonResponse
    {
        $currentGame = $gameRepository->findUserCurrentGame($this->currentUser->getId());

        if ($currentGame) return new JsonResponse(['message' => 'You cannot create a new game while you are already in an existing game.'], JsonResponse::HTTP_FORBIDDEN, []);

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
    public function joinGame(string $code, GameRepository $gameRepository, UserGameRepository $userGameRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $currentGame = $gameRepository->findUserCurrentGame($this->currentUser->getId());

        if ($currentGame) return new JsonResponse(['message' => 'You cannot join a new game while you are already in an existing game.'], JsonResponse::HTTP_FORBIDDEN, []);

        $game = $gameRepository->findOneBy(['code' => $code, 'status' => 'waiting']);

        if ($game) {
            $currentUserGame = $currentGame ? $userGameRepository->getUserInUserGame($currentGame->getId(), $this->currentUser->getId()) : null;

            if (!$currentUserGame) {
                $userGame = new UserGame();

                $userGame->setUserId($this->getUser());
                $userGame->setGameId($game);
                $userGame->setStatus('on');
                $userGame->setJoinedAt(new DateTimeImmutable());

                $entityManager->persist($userGame);

                $game->addUserGame($userGame);
                $game->setUpdateAt(new DateTimeImmutable());

                $entityManager->persist($game);
                $entityManager->flush();

                $context = SerializationContext::create()->setGroups(['joinGame']);
                $jsonGame = $serializer->serialize($game, 'json', $context);

                return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
            } 
            else return new JsonResponse(['message' => 'You are already in this game.'], JsonResponse::HTTP_BAD_REQUEST, []);
        } 
        else return new JsonResponse(['message' => 'No game found with this code.'], JsonResponse::HTTP_NOT_FOUND, []);
    }

    #[Route('api/game/start', name: 'app_game.startGame', methods: ['POST'])]
    #[OA\Tag(name: 'Game')]
    #[Security(name: 'Bearer')]
    public function startGame(GameRepository $gameRepository, UserGameRepository $userGameRepository, CardRepository $cardRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $currentGame = $gameRepository->findUserCurrentGame($this->currentUser->getId());

        if (!$currentGame) return new JsonResponse(['message' => 'You cannot start a game without being in a room.'], JsonResponse::HTTP_FORBIDDEN, []);

        if ($currentGame->getCreatorId()->getId() === $this->currentUser->getId()) {
            if ($currentGame->getStatus() === 'started') return new JsonResponse(['message' => 'You cannot start a game that is already in progress.'], JsonResponse::HTTP_FORBIDDEN, []);
            
            $usersGames = $userGameRepository->getUsersGame($currentGame->getId());

            if (count($usersGames) >= 2) {
                // Draw Cards To user
                foreach ($usersGames as $key => $userGame) {
                    $AllCards = $cardRepository->getUnassignedCards($currentGame->getId());
                    $cards = array_rand($AllCards, 7);

                    foreach ($cards as $key => $card) {
                        $userCard = new UserCard();

                        $userCard->setUserGameId($userGame);
                        $userCard->setCardId($AllCards[$card]);
                        $userCard->setObtainedAt(new DateTimeImmutable());
                        $userCard->setStatus('on');

                        $entityManager->persist($userCard);
                        
                        $userGame->addUserCard($userCard);
                        $entityManager->persist($userGame);

                    }

                    $entityManager->flush();
                }

                // Dealing the first card so that players can start playing.
                $availableCards = $cardRepository->getUnassignedCards($currentGame->getId());
                $availableCardsNumber = array_filter($availableCards, function ($card) {
                    return $card->getType() === 'number';
                });

                $firstCard = array_rand($availableCardsNumber);

                $currentGame->setUpdateAt(new DateTimeImmutable());
                $currentGame->setStatus('started');
                $currentGame->setLastCard($availableCards[$firstCard]);

                $entityManager->persist($currentGame);
                $entityManager->flush();
    
                return new JsonResponse(null, Response::HTTP_OK, []);
            }
            return new JsonResponse(['message' => 'You cannot start this game. A minimum of 2 players is required.'], Response::HTTP_BAD_REQUEST, []);
        };

        return new JsonResponse(['message' => 'Unauthorized, you are not the owner of this game.'], Response::HTTP_FORBIDDEN, []);
    }

    #[Route('api/game/leave', name: 'app_game.leaveGame', methods: ['POST'])]
    #[OA\Tag(name: 'Game')]
    #[Security(name: 'Bearer')]
    public function leaveGame(GameRepository $gameRepository, UserGameRepository $userGameRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $currentGame = $gameRepository->findUserCurrentGame($this->currentUser->getId());

        if (!$currentGame) return new JsonResponse(['message' => 'You cannot leave a game you are not currently present in.'], JsonResponse::HTTP_BAD_REQUEST, []);

        $currentUserGame = $currentGame ? $userGameRepository->getUserInUserGame($currentGame->getId(), $this->currentUser->getId()) : null;

        if ($currentGame->getCreatorId()->getId() === $this->currentUser->getId()) {
            foreach ($currentGame->getUserGames() as $key => $value) {
                $value->setStatus('quit');
                $entityManager->persist($value);
            };

            $currentGame->setStatus('quit');
            $currentGame->setUpdateAt(new DateTimeImmutable());
            $entityManager->persist($currentGame);
        } 
        else {
            $currentUserGame->setStatus('quit');
            $entityManager->persist($currentUserGame);

            // If there is only one person left
            $players = $gameRepository->getUsersIds($currentGame->getId());

            if ((count($players) - 1) === 1) {
                $currentGame->setStatus('end');
                $currentGame->setUpdateAt(new DateTimeImmutable());

                $entityManager->persist($currentGame);
            } 
        };
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
