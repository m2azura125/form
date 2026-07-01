<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SubmissionsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubmissionExportController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['q', 'status', 'tgl_dari', 'tgl_sampai', 'dl_dari', 'dl_sampai']);

        $filename = 'rekap-pengajuan-'.now()->format('YmdHi').'.xlsx';

        return Excel::download(new SubmissionsExport($filters), $filename);
    }
}
