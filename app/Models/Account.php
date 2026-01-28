<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'name',
        'type',
        'initial_balance',
        'color',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Calcula o saldo atual da conta
     */
    public function getCurrentBalance(): float
    {
        $income = $this->transactions()
            ->where('type', 'income')
            ->sum('amount');

        $expenses = $this->transactions()
            ->where('type', 'expense')
            ->sum('amount');

        return $this->initial_balance + $income - $expenses;
    }

    /**
     * Retorna o nome do tipo de conta traduzido
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'checking' => 'Conta Corrente',
            'savings' => 'Poupança',
            'credit_card' => 'Cartão de Crédito',
            'cash' => 'Dinheiro',
            'investment' => 'Investimento',
            default => $this->type,
        };
    }
}
