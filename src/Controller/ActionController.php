<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Game;
use App\Entity\UserCard;
use App\Entity\UserGame;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\UserCardRepository;
use App\Repository\UserGameRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ActionController extends AbstractController
{
    /**
     * @var User $currentUser
     */
    private $currentUser;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->currentUser = $tokenStorage->getToken()->getUser();
    }

    #[Route('api/game/stuff', name: 'app_action.stuff', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns an array of your game stuff',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: UserGame::class, groups: ['getUserStuff']))
        )
    )]
    #[OA\Tag(name: 'Action')]
    #[Security(name: 'Bearer')]
    public function getStuff(GameRepository $gameRepository, UserGameRepository $userGameRepository, SerializerInterface $serializer): JsonResponse
    {
        $currentGame = $gameRepository->findUserCurrentGame($this->currentUser->getId());

        if (!$currentGame) return new JsonResponse(['message' => 'No game.'], JsonResponse::HTTP_FORBIDDEN, []);

        $userGame = $userGameRepository->getUserInUserGame($currentGame->getId(), $this->currentUser->getId());

        $context = SerializationContext::create()->setGroups(['getUserStuff']);
        $jsonUserGame= $serializer->serialize($userGame, 'json', $context);

        return new JsonResponse($jsonUserGame, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('api/game/play/{id}/{color<\w+>?null}', name: 'app_action.play', methods: ['POST'])]
    #[OA\Tag(name: 'Action')]
    #[Security(name: 'Bearer')]
    public function play(int $id, string $color, GameRepository $gameRepository, UserGameRepository $userGameRepository, UserCardRepository $userCardRepository, CardRepository $cardRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $currentGame = $gameRepository->findUserCurrentGame($this->currentUser->getId());
        
        if (!$currentGame) return new JsonResponse(['message' => 'You cannot play a card without being in a game.'], JsonResponse::HTTP_FORBIDDEN, []);
        
        if ($this->isYourTurn($currentGame, $gameRepository)) {
            $userCard = $userCardRepository->find($id);

            if (!$userCard) return new JsonResponse(['message' => 'This card cannot be found in your stuff'], JsonResponse::HTTP_FORBIDDEN, []);

            $userGame = $userGameRepository->getUserInUserGame($currentGame->getId(), $this->currentUser->getId());

            if ($userCard && $userCard->getUserGameId()->getId() === $userGame->getId() && $userCard->getStatus() === 'on') {
                $card = $userCard->getCardId();

                if ($card->isIsWild()) {
                    if ($card->getType() === 'change_color') {
                        if ($color !== null && !in_array(strtolower($color), ['red', 'yellow', 'green', 'blue'])) return new JsonResponse(['message' => 'Invalid color !'], JsonResponse::HTTP_BAD_REQUEST, []);

                        $userCard->setStatus('off');
                        $userCard->setUsedAt(new DateTimeImmutable());
    
                        $currentGame->setLastCard($card);
                        $currentGame->setUpdateAt(new DateTimeImmutable());
                        $currentGame->setLastUser($this->currentUser);
                        $currentGame->setSelectedColor(strtolower($color));
                        $currentGame->setSpecialPlayed(false);
    
                        $entityManager->persist($userCard);
                        $entityManager->persist($currentGame);
    
                        $entityManager->flush();
    
                        return new JsonResponse(null, JsonResponse::HTTP_OK, []);
                    }
                    elseif ($card->getType() === 'draw_four') {
                        if ($color !== null && !in_array(strtolower($color), ['red', 'yellow', 'green', 'blue'])) return new JsonResponse(['message' => 'Invalid color !'], JsonResponse::HTTP_BAD_REQUEST, []);

                        $nextUserId = $this->getNextPlayerId($currentGame, $gameRepository);
                        $targetedUser = $userGameRepository->getUserInUserGame($currentGame->getId() ,$nextUserId);

                        if (!$targetedUser) return new JsonResponse(['message' => 'Next Player not Found'], JsonResponse::HTTP_NOT_FOUND, []);

                        $userCard->setStatus('off');
                        $userCard->setUsedAt(new DateTimeImmutable());
    
                        $currentGame->setLastCard($card);
                        $currentGame->setUpdateAt(new DateTimeImmutable());
                        $currentGame->setLastUser($this->currentUser);
                        $currentGame->setSelectedColor(strtolower($color));
                        $currentGame->setSpecialPlayed(false);
    
                        $entityManager->persist($userCard);
                        $entityManager->persist($currentGame);

                        // Give Two Cards to next Player
                        $availableCards = $cardRepository->getUnassignedCards($currentGame->getId());

                        if ($availableCards) {
                            $cards = array_rand($availableCards, 4);

                            foreach ($cards as $key => $card) {
                                $targetedUserCard = new UserCard();
        
                                $targetedUserCard->setUserGameId($userGame);
                                $targetedUserCard->setCardId($availableCards[$card]);
                                $targetedUserCard->setObtainedAt(new DateTimeImmutable());
                                $targetedUserCard->setStatus('on');
        
                                $entityManager->persist($targetedUserCard);
                                
                                $targetedUser->addUserCard($targetedUserCard);
                                $entityManager->persist($targetedUser);
        
                            }
                        }
    
                        $entityManager->flush();
                        
                        return new JsonResponse(null, JsonResponse::HTTP_OK, []);
                    }
                }
                elseif ($card->isIsSpecial()) {
                    if ($card->getType() === 'skip' && (($card->getColor() !== null && $card->getColor() === $currentGame->getLastCard()->getColor()) || ($card->getColor() === $currentGame->getSelectedColor()))) {
                        $userCard->setStatus('off');
                        $userCard->setUsedAt(new DateTimeImmutable());
    
                        $currentGame->setLastCard($card);
                        $currentGame->setUpdateAt(new DateTimeImmutable());
                        $currentGame->setLastUser($this->currentUser);
                        $currentGame->setSelectedColor(null);
                        $currentGame->setSpecialPlayed(false);
    
                        $entityManager->persist($userCard);
                        $entityManager->persist($currentGame);
    
                        $entityManager->flush();
    
                        return new JsonResponse(null, JsonResponse::HTTP_OK, []);
                    }
                    elseif ($card->getType() === 'reverse' && (($card->getColor() !== null && $card->getColor() === $currentGame->getLastCard()->getColor()) || ($card->getColor() === $currentGame->getSelectedColor()))) {
                        $userCard->setStatus('off');
                        $userCard->setUsedAt(new DateTimeImmutable());
    
                        $currentGame->setLastCard($card);
                        $currentGame->setUpdateAt(new DateTimeImmutable());
                        $currentGame->setLastUser($this->currentUser);
                        $currentGame->setSelectedColor(null);
                        $currentGame->setSpecialPlayed(false);
                        $currentGame->setReverse($currentGame->isReverse() ? false : true);
    
                        $entityManager->persist($userCard);
                        $entityManager->persist($currentGame);
    
                        $entityManager->flush();
    
                        return new JsonResponse(null, JsonResponse::HTTP_OK, []);
                    }
                    elseif ($card->getType() === 'draw_two' && (($card->getColor() !== null && $card->getColor() === $currentGame->getLastCard()->getColor()) || ($card->getColor() === $currentGame->getSelectedColor()))) {
                        $nextUserId = $this->getNextPlayerId($currentGame, $gameRepository);
                        $targetedUser = $userGameRepository->getUserInUserGame($currentGame->getId() ,$nextUserId);

                        if (!$targetedUser) return new JsonResponse(['message' => 'Next Player not Found'], JsonResponse::HTTP_NOT_FOUND, []);

                        $userCard->setStatus('off');
                        $userCard->setUsedAt(new DateTimeImmutable());
    
                        $currentGame->setLastCard($card);
                        $currentGame->setUpdateAt(new DateTimeImmutable());
                        $currentGame->setLastUser($this->currentUser);
                        $currentGame->setSelectedColor(null);
                        $currentGame->setSpecialPlayed(false);
    
                        $entityManager->persist($userCard);
                        $entityManager->persist($currentGame);

                        // Give Two Cards to next Player
                        $availableCards = $cardRepository->getUnassignedCards($currentGame->getId());

                        if ($availableCards) {
                            $cards = array_rand($availableCards, 2);

                            foreach ($cards as $key => $card) {
                                $targetedUserCard = new UserCard();
        
                                $targetedUserCard->setUserGameId($userGame);
                                $targetedUserCard->setCardId($availableCards[$card]);
                                $targetedUserCard->setObtainedAt(new DateTimeImmutable());
                                $targetedUserCard->setStatus('on');
        
                                $entityManager->persist($targetedUserCard);
                                
                                $targetedUser->addUserCard($targetedUserCard);
                                $entityManager->persist($userGame);
        
                            }
                        }
    
                        $entityManager->flush();
                        
                        return new JsonResponse(null, JsonResponse::HTTP_OK, []);
                    }
                }
                elseif ((!$currentGame->getSelectedColor() && $card->getColor() !== null && $card->getColor() === $currentGame->getLastCard()->getColor()) || ($card->getColor() === $currentGame->getSelectedColor())) {
                    $userCard->setStatus('off');
                    $userCard->setUsedAt(new DateTimeImmutable());

                    $currentGame->setLastCard($card);
                    $currentGame->setUpdateAt(new DateTimeImmutable());
                    $currentGame->setLastUser($this->currentUser);
                    $currentGame->setSelectedColor(null);
                    $currentGame->setSpecialPlayed(true);

                    $entityManager->persist($userCard);
                    $entityManager->persist($currentGame);

                    $entityManager->flush();

                    return new JsonResponse(null, JsonResponse::HTTP_OK, []);
                }
                else {
                    return new JsonResponse(['message' => 'Invalid User card'], JsonResponse::HTTP_FORBIDDEN, []);
                }
            }

            return new JsonResponse(['message' => 'User Card not Found'], JsonResponse::HTTP_FORBIDDEN, []);
        }

        return new JsonResponse(['message' => 'It\'s not your turn, so you cannot perform this action'], JsonResponse::HTTP_FORBIDDEN, []);
    }

    #[Route('api/game/draw', name: 'app_action.draw', methods: ['POST'])]
    #[OA\Tag(name: 'Action')]
    #[Security(name: 'Bearer')]
    public function draw(GameRepository $gameRepository, UserGameRepository $userGameRepository, CardRepository $cardRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $currentGame = $gameRepository->findUserCurrentGame($this->currentUser->getId());
        
        if (!$currentGame) return new JsonResponse(['message' => 'You cannot draw cards without being in a game.'], JsonResponse::HTTP_FORBIDDEN, []);
        
        if ($this->isYourTurn($currentGame, $gameRepository)) {
            $userGame = $userGameRepository->getUserInUserGame($currentGame->getId(), $this->currentUser->getId());

            if ($userGame) {
                $availableCards = $cardRepository->getUnassignedCards($currentGame->getId());
                $newCard = array_rand($availableCards);

                $userCard = new UserCard();

                $userCard->setUserGameId($userGame);
                $userCard->setCardId($availableCards[$newCard]);
                $userCard->setObtainedAt(new DateTimeImmutable());
                $userCard->setStatus('on');

                $entityManager->persist($userCard);
                
                $userGame->addUserCard($userCard);
                $entityManager->persist($userGame);

                $currentGame->setUpdateAt(new DateTimeImmutable());
                $currentGame->setLastUser($this->currentUser);
                $currentGame->setSpecialPlayed(true);

                $entityManager->persist($currentGame);
                $entityManager->flush();

                return new JsonResponse(null, JsonResponse::HTTP_OK, []);
            }
            else return new JsonResponse(['message' => 'User game not found'], JsonResponse::HTTP_NOT_FOUND, []);
        }

        return new JsonResponse(['message' => 'It\'s not your turn, so you cannot perform this action'], JsonResponse::HTTP_FORBIDDEN, []);
    }

    private function isYourTurn(Game $game, GameRepository $gameRepository): bool 
    {
        $players = $gameRepository->getUsersIds($game->getId());
        
        if ($game->getLastUser() === null) {
            return $players[0]['id'] == $this->currentUser->getId();
        }
        
        $lastUserId = $game->getLastUser()->getId();
        $currentIndex = array_search($lastUserId, array_column($players, 'id'));
        
        if ($currentIndex !== false) {
            $directionMultiplier = $game->isReverse() ? -1 : 1;
            $nextIndex = $currentIndex + $directionMultiplier;
        
            if ($game->isSpecialPlayed() === false && in_array($game->getLastCard()->getType(), ['skip', 'draw_two', 'draw_four'])) {
                $nextIndex += $directionMultiplier;
            }
        
            if (isset($players[$nextIndex])) {
                return $players[$nextIndex]['id'] == $this->currentUser->getId();
            }
        }
        
        return $players[0]['id'] == $this->currentUser->getId();
    } 

    private function getNextPlayerId(Game $game, GameRepository $gameRepository): int
    {
        $players = $gameRepository->getUsersIds($game->getId());
    
        $currentUserId = $this->currentUser->getId();
        $currentIndex = array_search($currentUserId, array_column($players, 'id'));
    
        if ($currentIndex !== false) {
            $directionMultiplier = $game->isReverse() ? -1 : 1;
            $nextIndex = $currentIndex + $directionMultiplier;
    
            if (isset($players[$nextIndex])) {
                return $players[$nextIndex]['id'];
            }
        }
    
        return $players[0]['id'];
    }
}