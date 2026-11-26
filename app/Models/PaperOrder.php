<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaperOrder extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'biaya_transaksi' => 'decimal:2',
            'laba_terealisasi' => 'decimal:2',
        ];
    }

    public function saham(): BelongsTo
    {
        return $this->belongsTo(Saham::class);
    }
}
