<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionBuktiDpController extends Controller
{
    public function __invoke(Submission $submission): StreamedResponse
    {
        abort_unless($submission->bukti_dp && Storage::disk('local')->exists($submission->bukti_dp), 404);

        return Storage::disk('local')->response($submission->bukti_dp);
    }
}
