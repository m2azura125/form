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
        Schema::create('persentase_bagi_hasils', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->decimal('toko', 5, 2);
            $table->decimal('krisna', 5, 2);
            $table->decimal('aldo', 5, 2);
            $table->string('diubah_oleh')->nullable();
            $table->timestamps();

            $table->unique(['tahun', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persentase_bagi_hasils');
    }
};
