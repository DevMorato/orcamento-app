<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_code',
        'name',
    ];

    // Gerar código automático ao criar família
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($family) {
            if (empty($family->family_code)) {
                $family->family_code = strtoupper(Str::random(8));
            }
        });
    }

    // Relacionamentos
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}