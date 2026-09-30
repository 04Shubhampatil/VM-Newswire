<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\MediaCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaImportController extends Controller
{
    public function create(): View
    {
        return view('admin.media.import', [
            'packages' => Package::ordered()->get(['id', 'name']),
            'result' => session('import_result'),
        ]);
    }

    public function store(Request $request, MediaCsvImporter $importer): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel', 'max:2048'],
            'update_existing' => ['boolean'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
        ]);

        try {
            $result = $importer->import(
                $request->file('file')->getRealPath(),
                $request->boolean('update_existing'),
                filled($data['package_id'] ?? null) ? Package::find($data['package_id']) : null,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->route('admin.media.import')
            ->with('import_result', $result)
            ->with('toast', "CSV import completed: {$result['imported']} imported, {$result['updated']} updated, {$result['skipped']} skipped, {$result['failed']} failed.");
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, MediaCsvImporter::COLUMNS);
            fputcsv($out, ['Example Business Daily', 'https://example.com', '', 'Business', '1']);
            fclose($out);
        }, 'media-outlets-template.csv', ['Content-Type' => 'text/csv']);
    }
}
