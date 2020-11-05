<?php

namespace App\Repository;

use App\Entity\NotUsedCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NotUsedCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotUsedCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotUsedCard[]    findAll()
 * @method NotUsedCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotUsedCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotUsedCard::class);
    }

    // /**
    //  * @return NotUsedCard[] Returns an array of NotUsedCard objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NotUsedCard
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
