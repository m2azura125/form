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
        Schema::table('submissions', function (Blueprint $table) {
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'qris', 'e_wallet'])->nullable()->after('biaya_jasa');
            $table->unsignedBigInteger('jumlah_bayar')->nullable()->after('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['metode_pembayaran', 'jumlah_bayar']);
        });
    }
};
