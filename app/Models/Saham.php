<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Saham extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::addGlobalScope('owner', function (Builder $query): void {
            $userId = Auth::id();

            if ($userId === null) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where($query->qualifyColumn('user_id'), $userId);
        });

        static::creating(function (Saham $saham): void {
            if ($saham->user_id === null && Auth::check()) {
                $saham->user_id = Auth::id();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'harga_beli' => 'decimal:2',
            'dividen_per_saham' => 'decimal:2',
            'kapitalisasi_pasar' => 'decimal:2',
            'per' => 'decimal:4',
            'pbv' => 'decimal:4',
            'roe' => 'decimal:4',
            'pertumbuhan_pendapatan' => 'decimal:4',
            'rasio_utang_ekuitas' => 'decimal:4',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SahamTransaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financials(): HasMany
    {
        return $this->hasMany(SahamFinancial::class);
    }

    public function corporateActions(): HasMany
    {
        return $this->hasMany(SahamCorporateAction::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SahamPost::class);
    }

    public function priceAlerts(): HasMany
    {
        return $this->hasMany(SahamPriceAlert::class);
    }

    public function paperOrders(): HasMany
    {
        return $this->hasMany(PaperOrder::class);
    }

    public function autoOrders(): HasMany
    {
        return $this->hasMany(AutoOrder::class);
    }
}
