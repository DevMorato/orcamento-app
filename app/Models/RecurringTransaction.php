<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'user_id',
        'category_id',
        'subcategory_id',
        'account_id',
        'type',
        'amount',
        'description',
        'establishment',
        'frequency',
        'day_of_month',
        'day_of_week',
        'start_date',
        'end_date',
        'next_due_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_due_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Retorna o label da frequência traduzido
     */
    public function getFrequencyLabel(): string
    {
        return match ($this->frequency) {
            'daily' => 'Diária',
            'weekly' => 'Semanal',
            'monthly' => 'Mensal',
            'yearly' => 'Anual',
            default => $this->frequency,
        };
    }

    /**
     * Calcula a próxima data de vencimento baseado na frequência
     */
    public function calculateNextDueDate(): Carbon
    {
        $current = $this->next_due_date ?? Carbon::now();

        return match ($this->frequency) {
            'daily' => $current->addDay(),
            'weekly' => $current->addWeek(),
            'monthly' => $current->addMonth()->day(min($this->day_of_month ?? $current->day, $current->addMonth()->daysInMonth)),
            'yearly' => $current->addYear(),
            default => $current->addMonth(),
        };
    }

    /**
     * Cria uma transação baseado nesta recorrência
     */
    public function createTransaction(): Transaction
    {
        $transaction = Transaction::create([
            'family_id' => $this->family_id,
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'recurring_transaction_id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'date' => $this->next_due_date,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'establishment' => $this->establishment,
            'description' => $this->description,
        ]);

        // Atualiza a próxima data de vencimento
        $this->next_due_date = $this->calculateNextDueDate();
        $this->save();

        return $transaction;
    }

    /**
     * Verifica se a recorrência está ativa e dentro do período
     */
    public function isDue(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->end_date && Carbon::now()->isAfter($this->end_date)) {
            return false;
        }

        return Carbon::now()->isAfter($this->next_due_date) || Carbon::now()->isSameDay($this->next_due_date);
    }
}
