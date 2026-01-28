<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'transactions:process-recurring';

    protected $description = 'Processa transações recorrentes que estão no vencimento';

    public function handle(): int
    {
        $this->info('Iniciando processamento de transações recorrentes...');

        $dueTransactions = RecurringTransaction::query()
            ->where('is_active', true)
            ->where('next_due_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->get();

        $processed = 0;

        foreach ($dueTransactions as $recurring) {
            try {
                $transaction = $recurring->createTransaction();
                $this->line("✓ {$recurring->description}: R$ " . number_format($recurring->amount, 2, ',', '.'));
                $processed++;
            } catch (\Exception $e) {
                $this->error("✗ {$recurring->description}: " . $e->getMessage());
            }
        }

        $this->info("Processadas {$processed} transações recorrentes.");

        return Command::SUCCESS;
    }
}
