<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Package;
use App\Services\EnquiryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryController extends Controller
{
    private const SORTS = ['created_at', 'name', 'status'];

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        [$sort, $direction] = $this->sort($request);

        return view('admin.enquiries.index', [
            'enquiries' => Enquiry::query()
                ->filter($filters)
                ->with('package:id,name')
                ->orderBy($sort, $direction)
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString(),
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
            'statusCounts' => Enquiry::query()->select('status')->selectRaw('count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'reviewCount' => Enquiry::query()->needsSpamReview()->count(),
            'packages' => Package::withTrashed()->ordered()->get(['id', 'name']),
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        return view('admin.enquiries.show', ['enquiry' => $enquiry->load(['package:id,name,slug,price,currency,deleted_at', 'emailLogs'])]);
    }

    public function updateStatus(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(EnquiryStatus::class)]]);
        $status = EnquiryStatus::from($data['status']);

        if ($status === EnquiryStatus::Spam) {
            $enquiry->markAsSpam('admin');
        } elseif ($enquiry->status === EnquiryStatus::Spam) {
            $enquiry->restoreFromSpam();
            $enquiry->update(['status' => $status]);
        } else {
            $enquiry->update(['status' => $status]);
        }

        return back()->with('toast', 'Enquiry status updated to '.$enquiry->status->label().'.');
    }

    /**
     * Bulk actions from the list: mark selected enquiries as Spam, or restore selected spam to New.
     * Never deletes anything.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bulk_action' => ['required', Rule::in(['spam', 'restore'])],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ], ['ids.required' => 'Select at least one enquiry first.']);

        $enquiries = Enquiry::whereKey($data['ids'])->get();
        $changed = 0;

        foreach ($enquiries as $enquiry) {
            if ($data['bulk_action'] === 'spam' && $enquiry->status !== EnquiryStatus::Spam) {
                $enquiry->markAsSpam('admin');
                $changed++;
            } elseif ($data['bulk_action'] === 'restore' && $enquiry->status === EnquiryStatus::Spam) {
                $enquiry->restoreFromSpam();
                $changed++;
            }
        }

        return back()->with('toast', $data['bulk_action'] === 'spam'
            ? "{$changed} enquir".($changed === 1 ? 'y' : 'ies').' marked as spam.'
            : "{$changed} enquir".($changed === 1 ? 'y' : 'ies').' restored to New.');
    }

    public function retryEmails(Enquiry $enquiry, EnquiryService $service): RedirectResponse
    {
        $count = $service->retryFailedEmails($enquiry);

        return back()->with($count ? 'toast' : 'toast_error', $count ? "{$count} email(s) queued for resending." : 'There are no failed emails to resend.');
    }

    /**
     * Streams matching enquiries as CSV without loading them all into memory.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads accents correctly
            fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'Company', 'Country', 'Package', 'Package Price At Enquiry', 'Press Releases', 'Status', 'Source Page', 'Message', 'Created At']);

            Enquiry::query()->filter($filters)->with('package:id,name')->orderBy('id')
                ->lazyById(500)
                ->each(function (Enquiry $e) use ($out) {
                    fputcsv($out, array_map([$this, 'csvSafe'], [
                        $e->id, $e->name, $e->email, $e->phone, $e->company, $e->country, $e->package_label,
                        $e->package_price_snapshot, $e->release_count, $e->status->label(), $e->source_page,
                        $e->message, $e->created_at->toDateTimeString(),
                    ]));
                });

            fclose($out);
        }, 'vm-newswire-enquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Neutralises spreadsheet formula injection (=, +, -, @ at the start of a cell).
     */
    public function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /**
     * @return array{search: ?string, status: ?string, flag: ?string, package: ?string, from: ?string, to: ?string}
     */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(EnquiryStatus::class)],
            'flag' => ['nullable', Rule::in(['review'])],
            'package' => ['nullable', 'string', 'max:20'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'search' => $data['search'] ?? null,
            'status' => $data['status'] ?? null,
            'flag' => $data['flag'] ?? null,
            'package' => $data['package'] ?? null,
            'from' => $data['from'] ?? null,
            'to' => $data['to'] ?? null,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function sort(Request $request): array
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        return [$sort, $direction];
    }
}
