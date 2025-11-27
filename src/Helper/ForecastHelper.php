<?php

namespace App\Helper;

use DateTimeImmutable;
use DateTime;
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

    /**
     * Calculates how many times a given transaction would repeat based on it's recurring type
     */
    public static function getTransactionMultiplerBetweenDates(TransactionRecurringType $type, DateTime $start_date, DateTime $end_date)
    {
        if($type === TransactionRecurringType::NoRepeat) {
            return 1;
        }

        if ($end_date <= $start_date) {
            return 0;
        }

        switch ($type) {
            case TransactionRecurringType::Daily:
                $interval = $start_date->diff($end_date);
                return $interval->days + 1;

            case TransactionRecurringType::Weekly:
                $days = $start_date->diff($end_date)->days;
                return intdiv($days, 7) + 1;

            case TransactionRecurringType::Monthly:
                // Count month boundaries
                $year_diff  = (int)$end_date->format('Y') - (int)$start_date->format('Y');
                $month_diff = (int)$end_date->format('m') - (int)$start_date->format('m');

                $total_months = ($year_diff * 12) + $month_diff;

                // If end day is >= start day, include the last month as a cycle
                if ((int)$end_date->format('d') >= (int)$start_date->format('d')) {
                    $total_months += 1;
                }

                return max(1, $total_months);

            default:
                return 1;
        }
    }
}