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
        Schema::table('sahams', function (Blueprint $table) {
            $table->decimal('kapitalisasi_pasar', 20, 2)->nullable();
            $table->decimal('per', 12, 4)->nullable();
            $table->decimal('pbv', 12, 4)->nullable();
            $table->decimal('roe', 12, 4)->nullable();
            $table->decimal('pertumbuhan_pendapatan', 12, 4)->nullable();
            $table->decimal('rasio_utang_ekuitas', 12, 4)->nullable();
        });

        Schema::create('saham_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saham_id')->constrained('sahams')->cascadeOnDelete();
            $table->string('periode', 20);
            $table->enum('jenis_periode', ['kuartal', 'tahunan']);
            $table->decimal('pendapatan', 20, 2)->nullable();
            $table->decimal('laba_operasi', 20, 2)->nullable();
            $table->decimal('laba_bersih', 20, 2)->nullable();
            $table->decimal('total_aset', 20, 2)->nullable();
            $table->decimal('total_liabilitas', 20, 2)->nullable();
            $table->decimal('arus_kas_operasi', 20, 2)->nullable();
            $table->string('sumber')->nullable();
            $table->timestamps();
            $table->unique(['saham_id', 'periode', 'jenis_periode']);
        });

        Schema::create('saham_corporate_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saham_id')->constrained('sahams')->cascadeOnDelete();
            $table->string('jenis');
            $table->date('tanggal_penting')->nullable();
            $table->decimal('nilai_per_saham', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('sumber')->nullable();
            $table->timestamps();
        });

        Schema::create('saham_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saham_id')->constrained('sahams')->cascadeOnDelete();
            $table->string('nama_penulis', 80);
            $table->text('isi');
            $table->timestamps();
        });

        Schema::create('saham_price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saham_id')->constrained('sahams')->cascadeOnDelete();
            $table->enum('kondisi', ['di_atas', 'di_bawah']);
            $table->decimal('harga_target', 15, 2);
            $table->enum('status', ['aktif', 'terpicu'])->default('aktif');
            $table->timestamp('terpicu_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('paper_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saham_id')->constrained('sahams')->cascadeOnDelete();
            $table->enum('jenis', ['beli', 'jual']);
            $table->integer('jumlah_lot');
            $table->decimal('harga', 15, 2);
            $table->decimal('laba_terealisasi', 20, 2)->default(0);
            $table->enum('sumber_order', ['manual', 'auto_order'])->default('manual');
            $table->timestamps();
        });

        Schema::create('auto_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saham_id')->constrained('sahams')->cascadeOnDelete();
            $table->enum('jenis', ['beli', 'jual']);
            $table->integer('jumlah_lot');
            $table->decimal('harga_pemicu', 15, 2);
            $table->enum('status', ['aktif', 'tereksekusi', 'dibatalkan'])->default('aktif');
            $table->timestamp('dijalankan_pada')->nullable();
            $table->foreignId('paper_order_id')->nullable()->constrained('paper_orders')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_orders');
        Schema::dropIfExists('paper_orders');
        Schema::dropIfExists('saham_price_alerts');
        Schema::dropIfExists('saham_posts');
        Schema::dropIfExists('saham_corporate_actions');
        Schema::dropIfExists('saham_financials');

        Schema::table('sahams', function (Blueprint $table) {
            $table->dropColumn([
                'kapitalisasi_pasar',
                'per',
                'pbv',
                'roe',
                'pertumbuhan_pendapatan',
                'rasio_utang_ekuitas',
            ]);
        });
    }
};
