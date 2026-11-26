<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoOrder extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'harga_pemicu' => 'decimal:2',
            'dijalankan_pada' => 'datetime',
        ];
    }

    public function saham(): BelongsTo
    {
        return $this->belongsTo(Saham::class);
    }

    public function paperOrder(): BelongsTo
    {
        return $this->belongsTo(PaperOrder::class);
    }
}
