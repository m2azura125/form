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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('asal_kampus', 150);
            $table->string('judul_alat', 150);
            $table->text('fitur');
            $table->date('tanggal_pengajuan');
            $table->date('deadline');
            $table->unsignedBigInteger('biaya_jasa')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('tanggal_pengajuan');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
