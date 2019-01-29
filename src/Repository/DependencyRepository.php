<?php

namespace Xima\DepmonBundle\Repository;

use Xima\DepmonBundle\Entity\Dependency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Dependency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dependency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dependency[]    findAll()
 * @method Dependency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DependencyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Dependency::class);
    }

    // /**
    //  * @return Dependency[] Returns an array of Dependency objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Dependency
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
