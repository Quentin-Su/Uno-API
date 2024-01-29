<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\UserGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 *
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
    * @return Game|null
    */
    public function findUserCurrentGame($userId): ?Game 
    {
        return $this->createQueryBuilder('g')
            ->join('App\Entity\UserGame', 'ug', 'WITH', 'g.id = ug.game_id')
            ->where('g.status NOT IN (:statuses)')
            ->andWhere('ug.user_id = :userId')
            ->andWhere('ug.status = :userGameStatus')
            ->setParameters([
                'statuses' => ['end', 'quit'],
                'userId' => $userId,
                'userGameStatus' => 'on',
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
    * @return array Returns an array of user IDs present in the targeted game
    */
    public function getUsersIds($gameId): array
    {
        return $this->createQueryBuilder('g')
            ->select('DISTINCT u.id')
            ->join('g.userGames', 'ug')
            ->join('ug.user_id', 'u')
            ->where('ug.status = :userGameStatus')
            ->andWhere('g.id = :gameId')
            ->setParameters([
                'userGameStatus' => 'on',
                'gameId' => $gameId,
            ])
            ->getQuery()
            ->getResult();
    }
}
