<?php

namespace App\Enum;

enum TransactionRecurringType: string
{
    case NoRepeat = 'Never';
    case Daily = 'Daily';
    case Weekly = 'Weekly';
    case Monthly = 'Monthly';
}