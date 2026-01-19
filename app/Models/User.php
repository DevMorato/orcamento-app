<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'family_id',
        'initial_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'initial_balance' => 'decimal:2',
        ];
    }

    /**
     * Permitir acesso ao painel Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Relacionamento com Family
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Relacionamento com Transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relacionamento com TransactionSplits
     */
    public function transactionSplits(): HasMany
    {
        return $this->hasMany(TransactionSplit::class);
    }

    /**
     * Calcular saldo atual do usuário
     */
    public function getCurrentBalance(): float
    {
        // Soma todas as receitas do usuário
        $income = $this->transactions()
            ->where('type', 'income')
            ->sum('amount');

        // Soma todas as despesas divididas do usuário
        $myExpenses = $this->transactionSplits()->sum('amount');

        return $this->initial_balance + $income - $myExpenses;
    }
}