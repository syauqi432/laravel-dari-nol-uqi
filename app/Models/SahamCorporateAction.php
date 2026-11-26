<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SahamCorporateAction extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_penting' => 'date',
            'nilai_per_saham' => 'decimal:2',
        ];
    }

    public function saham(): BelongsTo
    {
        return $this->belongsTo(Saham::class);
    }
}
