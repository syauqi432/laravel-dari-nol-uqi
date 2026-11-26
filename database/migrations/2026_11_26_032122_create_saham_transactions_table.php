<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saham_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saham_id')->constrained('sahams')->cascadeOnDelete();
            $table->enum('jenis', ['beli', 'jual']);
            $table->integer('jumlah_lot');
            $table->decimal('harga', 15, 2);
            $table->date('tanggal');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saham_transactions');
    }
};
