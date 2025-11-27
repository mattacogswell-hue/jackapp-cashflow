<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transaction;
use App\Enum\TransactionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use DateTimeImmutable;
use DateTime;
use App\Enum\TransactionRecurringType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Helper\ForecastHelper;

final class CashflowController extends AbstractController
{
    #[Route('/income', methods: ["POST"])]
    public function income(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $transaction = $serializer->deserialize($content, Transaction::class, "json");

        $transaction->setType(TransactionType::Income);

        if(!$transaction->getDate()) {
            $transaction->setDate(new DateTimeImmutable());
        }

        // Convert recurring JSON data into correct enum
        $transaction->setRecurringType(TransactionRecurringType::tryFromInsensitive($data['recurring']));

        if(!$transaction->getRecurringType()) {
            $transaction->setRecurringType(TransactionRecurringType::NoRepeat);
        }

        $errors = $validator->validate($transaction);

        if(count($errors) > 0) {
            return $this->json(["errors" => $errors], 422);
        }

        $em->persist($transaction);
        $em->flush();

        return $this->json([
            $transaction,
            201
        ]);
    }

    #[Route('/expense', methods: ["POST"])]
    public function expense(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $transaction = $serializer->deserialize($content, Transaction::class, "json");

        $transaction->setType(TransactionType::Expense);

        if(!$transaction->getDate()) {
            $transaction->setDate(new DateTimeImmutable());
        }

        // Convert recurring JSON data into correct enum
        $transaction->setRecurringType(TransactionRecurringType::tryFromInsensitive($data['recurring']));

        if(!$transaction->getRecurringType()) {
            $transaction->setRecurringType(TransactionRecurringType::NoRepeat);
        }

        $errors = $validator->validate($transaction);

        if(count($errors) > 0) {
            return $this->json(["errors" => $errors], 422);
        }

        $em->persist($transaction);
        $em->flush();

        return $this->json([
            $transaction,
            201
        ]);
    }

    #[Route('/forecast/{months}', methods: ["GET"])]
    public function forecast(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        int $months): JsonResponse
    {
        $today = new DateTimeImmutable();
        $endDate = $today->modify("+$months months")->setTime(0, 0, 0);

        $monthlyBalances = ForecastHelper::initializeMonthlyBalances($today, $endDate);

        // --- PHASE 1: Process All Base Transactions (Non-Recurring + First Occurrence) ---
        $allRelevantTransactions = $em->getRepository(Transaction::class)->createQueryBuilder('t')
            ->where('t.date >= :yesterday')
            ->andWhere('t.date < :end')
            ->setParameter('yesterday', $today->modify('-1 day'))
            ->setParameter('end', $endDate)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($allRelevantTransactions as $transaction) {
            $amount = $transaction->getAmount();
            $operation = ($transaction->getType() === TransactionType::Income) ? 'add' : 'sub';
            $monthKey = $transaction->getDate()->format('Y-m-01');

            if (isset($monthlyBalances[$monthKey])) {
                // Apply the base transaction amount
                $currentBalance = $monthlyBalances[$monthKey];
                $monthlyBalances[$monthKey] = $currentBalance->$operation($amount);
            }
        }

        // --- PHASE 2: Project Subsequent Recurring Transactions ---
        $recurringTransactions = $em->getRepository(Transaction::class)->findAllRecurringTransactions();

        foreach ($recurringTransactions as $transaction) {
            // Get the base amount and type
            $amount = $transaction->getAmount();
            $recurringType = $transaction->getRecurringType();
            $startDate = $transaction->getDate();
            $operation = ($transaction->getType() === TransactionType::Income) ? 'add' : 'sub';

            // Set the starting date for projection. 
            // We start from the day *after* the first occurrence or today, whichever is later.
            $nextOccurrenceDate = $startDate->modify(ForecastHelper::getRecurrenceInterval($recurringType));
            
            // Ensure we skip the first occurrence if it was already included in PHASE 1
            if ($nextOccurrenceDate <= $today) {
                // Move projection date past the current date
                while ($nextOccurrenceDate <= $today) {
                    $nextOccurrenceDate = $nextOccurrenceDate->modify(ForecastHelper::getRecurrenceInterval($recurringType));
                }
            }
            
            // Loop until the projection date exceeds the forecast end date
            $projectionDate = $nextOccurrenceDate;

            while ($projectionDate < $endDate) {
                $monthKey = $projectionDate->format('Y-m-01');
                
                if (isset($monthlyBalances[$monthKey])) {
                    $currentBalance = $monthlyBalances[$monthKey];
                    $monthlyBalances[$monthKey] = $currentBalance->$operation($amount);
                }

                // Advance the date based on the recurrence type
                $projectionDate = $projectionDate->modify(ForecastHelper::getRecurrenceInterval($recurringType));
                if ($projectionDate === false) break;
            }
        }

        // Format for final output
        $formattedForecast = [];
        foreach ($monthlyBalances as $monthKey => $balanceObject) {
            $balanceString = (string) $balanceObject->round(2);

            $monthDate = new DateTimeImmutable($monthKey);
            $readableMonth = $monthDate->format('F Y');
            
            $formattedForecast[] = [
                'Month' => $readableMonth,
                'Projected Balance' => $balanceString,
            ];
        }

        return $this->json([
            'Forecast Summary' => $formattedForecast, // New key for better context
        ]);
    }

    #[Route('/transactions', methods: ["GET"])]
    public function index(
        EntityManagerInterface $em,
        SerializerInterface $serializer): JsonResponse
    {
        $transactions = $em->getRepository(Transaction::class)->findAll();

        $json_content = $serializer->serialize($transactions, "json", [
            DateTimeNormalizer::FORMAT_KEY => 'd-m-Y',
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['id']
        ]);
        return JsonResponse::fromJsonString($json_content);
    }

    #[Route('/balance', name: 'balance', methods: ["GET"])]
    public function balance(EntityManagerInterface $em): JsonResponse
    {
        $today = new DateTime();
        $transactions = $em->getRepository(Transaction::class)->findPastTransactions();

        $balance = 0;
        foreach($transactions as $transaction) {
            $multipler = ForecastHelper::getTransactionMultiplerBetweenDates(
                $transaction->getRecurringType(),
                $transaction->getdate(),
                $today
            );

            switch ($transaction->getType()) {
                case TransactionType::Income:
                    $balance += ($multipler * $transaction->getAmount());
                    break;
                case TransactionType::Expense:
                    $balance -= ($multipler * $transaction->getAmount());
                    break;
                default:
                    break;
            }
        }

        return $this->json([
            'Current Balance' => $balance instanceof \BcMath\Number
                ? $balance->round(2)
                : round($balance, 2),
        ]);
    }
}
