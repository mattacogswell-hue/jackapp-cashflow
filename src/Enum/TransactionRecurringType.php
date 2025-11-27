<?php

namespace App\Enum;

enum TransactionRecurringType: string
{
    case NoRepeat = 'Never';
    case Daily = 'Daily';
    case Weekly = 'Weekly';
    case Monthly = 'Monthly';

    /**
     * Attempts to find an enum case matching the string, ignoring case.
     */
    public static function tryFromInsensitive(string $value): ?self
    {
        $lowerValue = strtolower($value);

        foreach (self::cases() as $case) {
            if (strtolower($case->value) === $lowerValue) {
                return $case;
            }
        }
        return null;
    }
}