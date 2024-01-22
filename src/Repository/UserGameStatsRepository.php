<?php

namespace App\Repository;

use App\Entity\UserGameStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserGameStats>
 *
 * @method UserGameStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGameStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGameStats[]    findAll()
 * @method UserGameStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGameStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGameStats::class);
    }

//    /**
//     * @return UserGameStats[] Returns an array of UserGameStats objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserGameStats
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
