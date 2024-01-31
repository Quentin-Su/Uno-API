<?php

namespace App\Repository;

use App\Entity\UserGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserGame>
 *
 * @method UserGame|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGame|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGame[]    findAll()
 * @method UserGame[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGame::class);
    }

    /**
    * @return UserGame|null Returns the targeted UserGame
    */
    public function getUserInUserGame($gameId, $userId): ?UserGame
    {
        return $this->createQueryBuilder('u')
            ->where('u.game_id = :gameId')
            ->andWhere('u.user_id = :userId')
            ->setParameters([
                'gameId' => $gameId,
                'userId' => $userId
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
    * @return UserGame[]|[] Returns an array of UserGame objects
    */
    public function getUsersGame($gameId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.game_id = :gameId')
            ->andWhere('u.status = :status')
            ->setParameters([
                'gameId' => $gameId,
                'status' => 'on'
            ])
            ->getQuery()
            ->getResult();
    }
}
