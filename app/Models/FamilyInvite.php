<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FamilyInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'invited_by',
        'email',
        'token',
        'role',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invite) {
            if (empty($invite->token)) {
                $invite->token = Str::random(64);
            }
            if (empty($invite->expires_at)) {
                $invite->expires_at = Carbon::now()->addDays(7);
            }
        });
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Verifica se o convite está expirado
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Verifica se o convite foi aceito
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Verifica se o convite é válido
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isAccepted();
    }

    /**
     * Aceita o convite e associa o usuário à família
     */
    public function accept(User $user): void
    {
        $user->update([
            'family_id' => $this->family_id,
            'role' => $this->role,
        ]);

        $this->update([
            'accepted_at' => Carbon::now(),
        ]);
    }

    /**
     * Retorna o label do role traduzido
     */
    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrador',
            'member' => 'Membro',
            'viewer' => 'Visualizador',
            default => $this->role,
        };
    }
}
