<?php

namespace App\Repository;

use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 *
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function save(Expense $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Expense $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Expense[] Returns an array of Expense objects
     */
    public function search(?int $categoryID, ?string $priceMin, ?string $priceMax, ?string $date): array
    {
        $queryBuilder = $this->createQueryBuilder('e');

        if ($categoryID)
            $queryBuilder->andWhere('e.categoryID = :categoryID');

        if ($priceMin)
            $queryBuilder->andWhere('e.price >= :priceMin');

        if ($priceMax)
            $queryBuilder->andWhere('e.price <= :priceMax');

        if ($date)
            $queryBuilder->andWhere('e.date = :date');

        if ($categoryID)
            $queryBuilder->setParameter('categoryID', $categoryID);

        if ($priceMin)
            $queryBuilder->setParameter('priceMin', $priceMin);

        if ($priceMax)
            $queryBuilder->setParameter('priceMax', $priceMax);

        if ($date)
            $queryBuilder->setParameter('date', $date);

        return $queryBuilder
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array[] Returns an array prices grouped by date
     */
    public function aggregateByDate(): array
    {
        $this->getEntityManager()->getConfiguration()
            ->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
        $this->getEntityManager()->getConfiguration()
            ->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder
            ->select('SUM(e.price * e.quantity) AS amount', 'YEAR(e.date) AS year', 'MONTH(e.date) AS month')
            ->groupBy('year, month');

        return $queryBuilder
            ->addOrderBy('year', 'DESC')
            ->addOrderBy('month', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
