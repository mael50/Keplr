<?php

namespace App\Repository;

use App\Entity\GithubRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<\App\Entity\GithubRepository>
 */
class GithubRepositoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GithubRepository::class);
    }

    public function save(GithubRepository $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    public function findBySearchTerm(string $term, $user)
    {
        return $this->createQueryBuilder('g')
            ->where('(g.name LIKE :term OR g.owner LIKE :term) AND g.user != :user')
            ->setParameter('term', '%' . $term . '%')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
