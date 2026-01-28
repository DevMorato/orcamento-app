<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'category_id',
        'amount',
        'month',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calcula o valor gasto neste orçamento
     */
    public function getSpentAmount(): float
    {
        return Transaction::query()
            ->where('family_id', $this->family_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->sum('amount');
    }

    /**
     * Calcula a porcentagem utilizada do orçamento
     */
    public function getPercentageUsed(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return round(($this->getSpentAmount() / $this->amount) * 100, 1);
    }

    /**
     * Calcula o valor restante do orçamento
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->amount - $this->getSpentAmount());
    }

    /**
     * Verifica se o orçamento está acima do limite
     */
    public function isOverBudget(): bool
    {
        return $this->getSpentAmount() > $this->amount;
    }

    /**
     * Verifica se o orçamento está em alerta (acima de 80%)
     */
    public function isWarning(): bool
    {
        $percentage = $this->getPercentageUsed();
        return $percentage >= 80 && $percentage < 100;
    }
}
