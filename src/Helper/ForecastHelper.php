<?php

namespace App\Helper;

use DateTimeImmutable;
use App\Enum\TransactionRecurringType;

class ForecastHelper
{
    /**
     * Creates an associative array to hold the cash flow balance for the months between the given dates
     */
    public static function initializeMonthlyBalances(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $monthlyBalances = [];
        $zeroBcMath = new \BcMath\Number('0.00');
        
        $currentMonthStart = $startDate->setDate((int)$startDate->format('Y'), (int)$startDate->format('m'), 1);

        // Loop through each month, one by one, until the end date is reached
        while ($currentMonthStart < $endDate) {
            $monthKey = $currentMonthStart->format('Y-m-01');
            $monthlyBalances[$monthKey] = $zeroBcMath;
            $currentMonthStart = $currentMonthStart->modify('+1 month');
        }
        
        return $monthlyBalances;
    }

    /**
     * Maps enum to date modify strings
     */
    public static function getRecurrenceInterval(TransactionRecurringType $type): string
    {
        return match ($type) {
            TransactionRecurringType::Daily => '1 day',
            TransactionRecurringType::Weekly => '1 week',
            TransactionRecurringType::Monthly => '1 month',
            default => '1 year', // A safe, though unlikely, default fallback
        };
    }
}