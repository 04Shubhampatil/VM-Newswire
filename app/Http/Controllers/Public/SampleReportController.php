<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\SampleReport;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SampleReportController extends Controller
{
    public function index(): View
    {
        return view('public.sample-reports', [
            'packages' => Package::active()->ordered()->with(['currentReport', 'featuredMedia'])->get(),
        ]);
    }

    /**
     * Streams the package's current sample report. The stored path is never exposed.
     */
    public function download(Package $package): StreamedResponse
    {
        $report = $this->currentReport($package);

        return Storage::disk($report->disk)->download($report->file_path, $report->download_name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Same file served inline, for the in-page preview (iframe) and "open in new tab".
     */
    public function view(Package $package): StreamedResponse
    {
        $report = $this->currentReport($package);

        return Storage::disk($report->disk)->response($report->file_path, $report->download_name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function currentReport(Package $package): SampleReport
    {
        abort_unless($package->is_active, 404);

        $report = $package->currentReport;
        abort_unless($report && Storage::disk($report->disk)->exists($report->file_path), 404);

        return $report;
    }
}
