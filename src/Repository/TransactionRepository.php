<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\TransactionType;
use DateTime;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Finds all transactions ordered by date ascending.
     * @return Transaction[]
     */
    public function findAllOrderedByDateAsc(): array
    {
        return $this->createQueryBuilder('t') 
            ->orderBy('t.date', 'ASC') 
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all upcoming transactions ordered by date ascending.
     * @return Transaction[]
     */
    public function findAllUpcomingTransactions(): array
    {
        return $this->createQueryBuilder('t') 
            ->where('t.date > :now')
            ->setParameter('now', $now)
            ->orderBy('t.date', 'ASC') 
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all upcoming income transactions ordered by date ascending.
     * @return Transaction[]
     */
    public function findAllUpcomingIncome(): array
    {
        $now = new DateTime();

        return $this->createQueryBuilder('t') 
            ->where('t.date > :now')
            ->andWhere('t.type = :type')
            ->setParameter('type', TransactionType::Income)
            ->setParameter('now', $now)
            ->orderBy('t.date', 'ASC') 
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all upcoming expense transactions ordered by date ascending.
     * @return Transaction[]
     */
    public function findAllUpcomingExpenses(): array
    {
        $now = new DateTime();

        return $this->createQueryBuilder('t') 
            ->where('t.date > :now')
            ->andWhere('t.type = :type')
            ->setParameter('type', TransactionType::Expense)
            ->setParameter('now', $now)
            ->orderBy('t.date', 'ASC') 
            ->getQuery()
            ->getResult();
    }
}
