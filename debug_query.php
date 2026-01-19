<?php

use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionSplit;

// Assuming User 2 based on previous tinker output
$user = User::find(2);
auth()->login($user);

echo "User: " . $user->name . " (ID: " . $user->id . ")\n";

$monthStart = now()->startOfMonth();
$monthEnd = now()->endOfMonth();

echo "Month Range: " . $monthStart->toDateTimeString() . " - " . $monthEnd->toDateTimeString() . "\n";

// Check raw splits
$splitsCount = $user->transactionSplits()->count();
echo "Total Splits for User: " . $splitsCount . "\n";

// Check Query from StatsOverview
$expenses = $user->transactionSplits()
    ->whereHas('transaction', function ($q) use ($monthStart, $monthEnd) {
        $q->whereBetween('date', [$monthStart, $monthEnd]);
    })
    ->sum('amount');

echo "StatsOverview Expenses: " . $expenses . "\n";

// Check Transactions directly
$txCount = Transaction::where('user_id', $user->id)->count();
echo "Transactions Created by User: " . $txCount . "\n";

// Dump last transaction
$lastTx = Transaction::latest()->first();
echo "Last Transaction ID: " . $lastTx->id . "\n";
echo "Last Transaction Date: " . $lastTx->date->toDateTimeString() . "\n"; // Check if date is within range
echo "Last Transaction Splits Count: " . $lastTx->splits()->count() . "\n";

if ($lastTx->splits()->count() > 0) {
    echo "Last Split User ID: " . $lastTx->splits->first()->user_id . "\n";
    echo "Last Split Amount: " . $lastTx->splits->first()->amount . "\n";
}
