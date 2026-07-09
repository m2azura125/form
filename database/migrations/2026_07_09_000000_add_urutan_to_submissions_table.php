<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->nullable()->after('id');
            $table->index('urutan');
        });

        DB::table('submissions')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $id, int $index): void {
                DB::table('submissions')->where('id', $id)->update(['urutan' => $index + 1]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['urutan']);
            $table->dropColumn('urutan');
        });
    }
};
