<?php

namespace App\Enum;

enum TransactionType: string
{
    case Income = 'Income';
    case Expense = 'Expense';
}