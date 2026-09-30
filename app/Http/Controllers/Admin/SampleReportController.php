<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\SampleReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SampleReportController extends Controller
{
    public function index(): View
    {
        return view('admin.sample-reports.index', [
            'packages' => Package::ordered()->with('currentReport')->get(['id', 'name', 'slug', 'is_active', 'display_order']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.sample-reports.create', [
            'packages' => Package::ordered()->get(['id', 'name']),
            'selected' => $request->integer('package') ?: null,
        ]);
    }

    /**
     * Uploads a PDF and makes it the package's current report. Older reports for the
     * package are removed (files included) so only one current report is kept.
     */
    public function store(Request $request): RedirectResponse
    {
        $config = config('vmnewswire.sample_reports');

        $validated = $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'file' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.$config['max_kb']],
        ], [], ['file' => 'PDF']);

        $file = $request->file('file');

        // Extension and MIME are checked above; also check the PDF signature itself.
        $handle = fopen($file->getRealPath(), 'rb');
        $signature = fread($handle, 5);
        fclose($handle);
        if ($signature !== '%PDF-') {
            return back()->withErrors(['file' => 'The file is not a valid PDF.'])->withInput();
        }

        $package = Package::findOrFail($validated['package_id']);
        $disk = $config['disk'];
        $path = $file->storeAs($config['directory'], Str::uuid().'.pdf', $disk);

        DB::transaction(function () use ($package, $file, $disk, $path) {
            $old = $package->sampleReports()->get();

            $package->sampleReports()->create([
                'file_name' => Str::limit(basename(str_replace('\\', '/', $file->getClientOriginalName())), 200, ''),
                'disk' => $disk,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'uploaded_at' => now(),
            ]);

            foreach ($old as $report) {
                Storage::disk($report->disk)->delete($report->file_path);
                $report->delete();
            }
        });

        return redirect()->to($request->input('return_to') === 'package'
            ? route('admin.packages.edit', $package)
            : route('admin.sample-reports.index'))
            ->with('toast', "Sample report uploaded for {$package->name}.");
    }

    public function download(SampleReport $report): StreamedResponse
    {
        abort_unless(Storage::disk($report->disk)->exists($report->file_path), 404);

        return Storage::disk($report->disk)->download($report->file_path, $report->file_name, ['Content-Type' => 'application/pdf']);
    }

    public function destroy(SampleReport $report): RedirectResponse
    {
        Storage::disk($report->disk)->delete($report->file_path);
        $report->delete();

        return back()->with('toast', 'Sample report removed.');
    }
}
