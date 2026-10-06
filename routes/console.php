<?php

use App\Models\Submission;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pengajuan:pindah-bulan', function () {
    $jumlah = Submission::pindahkanKeBulanIni();
    $this->info("{$jumlah} pengajuan dipindah ke bulan ini.");
})->purpose('Pindahkan pengajuan Baru/Diproses dari bulan lalu ke bulan ini dengan sisa deadline yang sama');

Schedule::command('pengajuan:pindah-bulan')->monthlyOn(1, '00:05');
