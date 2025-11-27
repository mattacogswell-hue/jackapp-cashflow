Cashflow Forecast Test
Basic App to log transaction and forecast cashflow N months in the future.

API Endpoints
POST /income - creates an income transaction, post with a JSON of transaction values
{
    "amount": 100, // required
    "recurring": "daily", // exclude for non recurring transactions
    "date": "11-11-2025", // when excluded with default to today
}

POST /expense - creates an expense transaction, post with a JSON of transaction values
{
    "amount": 100, // required
    "recurring": "daily", // exclude for non recurring transactions
    "date": "11-11-2025", // when excluded with default to today
}

GET /transactions - lists all transactions in the database
{
    {
        "Transaction Date": "29-11-2025",
        "Amount": "100",
        "type": "Income",
        "Recurring": "Never"
    },
    {
        "Transaction Date": "30-11-2025",
        "Amount": "50",
        "type": "Income",
        "Recurring": "Weekly"
    },
}

GET /balance - calculates the current balance based on transactions upto the current date
GET /forecast/{months} - calculates a cashflow forecast based on future and recurring transactions
{
    "Forecast Summary": [
        {
            "Month": "November 2025",
            "Projected Balance": "106.00"
        },
        {
            "Month": "December 2025",
            "Projected Balance": "62.00"
        },
        {
            "Month": "January 2026",
            "Projected Balance": "262.00"
        },
        {
            "Month": "February 2026",
            "Projected Balance": "252.00"
        }
    ]
}