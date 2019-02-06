<?php

namespace Xima\DepmonBundle\Repository;

use Xima\DepmonBundle\Entity\Dependency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Xima\DepmonBundle\Entity\Form\Filter;

/**
 * @method Dependency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dependency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dependency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DependencyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Dependency::class);
    }

    /**
     * @param Filter|null $demand
     * @return array|mixed
     */
    public function findAll(Filter $demand = null) {

        $qb = $this->createQueryBuilder('dependency');
        $expr = $qb->expr();

        if ($demand) {

            if ($demand->getDependencySearch()) {
                $qb
                    ->andWhere(
                        $expr->like('dependency.name', ':name')
                    )
                    ->setParameter('name', '%' . $demand->getDependencySearch() . '%')
                ;
            }

            if ($demand->getProjects()) {
                $qb
                    ->andWhere(
                        $expr->in('dependency.project', ':projects')
                    )
                    ->setParameter('projects', $demand->getProjects())
                ;
            }

            if ($demand->getStates()) {
                $qb
                    ->andWhere(
                        $expr->in('dependency.state', ':states')
                    )
                    ->setParameter('states', $demand->getStates())
                ;
            }

            if (!$demand->isShowIndirectlyDependencies()) {
                $qb
                    ->andWhere(
                        $expr->isNotNull('dependency.required')
                    )
                ;
            }

        }
        return $qb->getQuery()->getResult();
    }
}
