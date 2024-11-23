<?php

namespace App\Repository;

use App\Entity\SpaceShip;
use App\Filter\SpaceshipFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class SpaceShipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spaceship::class);
    }
    public function findAllWithQueryParams(int $offset, int $limit, ?string $searchQuery, string $sortBy = 'name', string $order = 'ASC'): array
    {

        $query = $this->createQueryBuilder('s')->where('1 = 1');
    
        if ($searchQuery) {
            $query
                ->where( $query->expr()->like('LOWER(s.name)', ':searchQuery'))
                ->setParameter('searchQuery', '%' . trim(strtolower($searchQuery)) . '%');
        }
        if ($sortBy == 'likes') {
            $query
                ->leftJoin('s.likes', 'l')
                ->groupBy('s.id')
                ->orderBy('COUNT(l)', "DESC");
        } else {
            $query
                ->orderBy('s.' . $sortBy, $order)
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }
    public function findAll(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findLatest(int $limit): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
