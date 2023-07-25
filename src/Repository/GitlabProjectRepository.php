<?php

namespace App\Repository;

use App\Entity\GitlabProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GitlabProject>
 *
 * @method GitlabProject|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitlabProject|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitlabProject[]    findAll()
 * @method GitlabProject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GitlabProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitlabProject::class);
    }

//    /**
//     * @return GitlabProject[] Returns an array of GitlabProject objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GitlabProject
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
