<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function getUnassignedCards($gameId)
    {
        $query = $this->getEntityManager()
            ->createQuery("SELECT c FROM App\Entity\Card c LEFT JOIN App\Entity\UserCard uc WITH c.id = uc.card_id AND uc.status = 'on' LEFT JOIN App\Entity\UserGame ug WITH uc.user_game_id = ug.id AND ug.game_id = :gameId WHERE uc.id IS NULL")
            ->setParameter(':gameId', $gameId);

        return $query->getResult();
    }
}
