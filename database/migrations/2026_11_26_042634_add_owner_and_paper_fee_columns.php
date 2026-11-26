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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_investment_owner')->nullable()->unique();
        });

        Schema::table('sahams', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('paper_orders', function (Blueprint $table) {
            $table->decimal('biaya_transaksi', 20, 2)->default(0)->after('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paper_orders', function (Blueprint $table) {
            $table->dropColumn('biaya_transaksi');
        });

        Schema::table('sahams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['is_investment_owner']);
            $table->dropColumn('is_investment_owner');
        });
    }
};
