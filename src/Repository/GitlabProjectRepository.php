<?php

namespace App\Repository;

use App\Entity\GitlabProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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

    /**
     * @throws NonUniqueResultException
     */
    public function findByGitlabId(int $id): ?GitlabProject
    {
        return $this->createQueryBuilder('g')
            ->where('g.gitlab_id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(GitlabProject $project): GitlabProject
    {
        $this->getEntityManager()->persist($project);
        $this->getEntityManager()->flush();

        return $project;
    }

    public function delete(GitlabProject $project): void
    {
        $this->getEntityManager()->remove($project);
        $this->getEntityManager()->flush();
    }
}
