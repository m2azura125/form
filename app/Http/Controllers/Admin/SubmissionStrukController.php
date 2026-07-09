<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Contracts\View\View;

class SubmissionStrukController extends Controller
{
    public function __invoke(Submission $submission): View
    {
        return view('admin.struk', ['submission' => $submission]);
    }
}
