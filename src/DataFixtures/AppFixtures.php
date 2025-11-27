<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Enum\TransactionRecurringType;
use DateTime;
use BcMath\Number;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $transaction = new Transaction();
        $transaction
            ->setAmount(new Number(100.00))
            ->setType(TransactionType::Income)
            ->setRecurringType(TransactionRecurringType::NoRepeat)
            ->setDate(new DateTime("2025-11-29"));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction
            ->setAmount(new Number(50.00))
            ->setType(TransactionType::Income)
            ->setRecurringType(TransactionRecurringType::Weekly)
            ->setDate(new DateTime("2025-11-30"));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction
            ->setAmount(new Number(50.00))
            ->setType(TransactionType::Expense)
            ->setRecurringType(TransactionRecurringType::NoRepeat)
            ->setDate(new DateTime("2025-11-29"));
        $manager->persist($transaction);

        $transaction = new Transaction();
        $transaction
            ->setAmount(new Number(200.00))
            ->setType(TransactionType::Expense)
            ->setRecurringType(TransactionRecurringType::NoRepeat)
            ->setDate(new DateTime("2025-12-03"));
        $manager->persist($transaction);

        $manager->flush();
    }
}
