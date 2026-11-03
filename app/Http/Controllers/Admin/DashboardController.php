<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmailDeliveryStatus;
use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Enquiry;
use App\Models\Faq;
use App\Models\MediaOutlet;
use App\Models\Package;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $thisWeek = Enquiry::notSpam()->where('created_at', '>=', now()->subDays(7))->count();
        $previousWeek = Enquiry::notSpam()->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        $daily = Enquiry::notSpam()->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (Enquiry $enquiry) => $enquiry->created_at->toDateString());

        $series = collect(range(13, 0))->map(function (int $daysAgo) use ($daily) {
            $date = now()->subDays($daysAgo);

            return ['date' => $date, 'count' => $daily[$date->toDateString()] ?? 0];
        });

        // Spam has its own tab under Enquiries and is left out of every dashboard figure.
        $byStatus = collect(EnquiryStatus::cases())->reject(fn (EnquiryStatus $s) => $s === EnquiryStatus::Spam)->mapWithKeys(fn (EnquiryStatus $status) => [
            $status->value => Enquiry::where('status', $status)->count(),
        ]);

        return view('admin.dashboard', [
            'metrics' => [
                'total' => Enquiry::notSpam()->count(),
                'new' => $byStatus[EnquiryStatus::New->value],
                'last30' => Enquiry::notSpam()->where('created_at', '>=', now()->subDays(30))->count(),
                'thisWeek' => $thisWeek,
                'previousWeek' => $previousWeek,
                'packages' => Package::active()->count(),
                'outlets' => MediaOutlet::active()->count(),
                'faqs' => Faq::active()->count(),
            ],
            'byStatus' => $byStatus,
            'series' => $series,
            'attention' => array_filter([
                'failedEmails' => EmailLog::where('delivery_status', EmailDeliveryStatus::Failed)->count(),
                'packagesWithoutReport' => Package::active()->doesntHave('sampleReports')->pluck('name'),
                'inactivePackages' => Package::where('is_active', false)->count(),
                'outletsWithoutPoster' => MediaOutlet::active()->whereNull('poster_path')->count(),
                'highlightedOutlets' => MediaOutlet::active()->where('is_highlighted', true)->count(),
            ]),
            'recent' => Enquiry::notSpam()->with('package:id,name')->latest()->limit(8)->get(),
            'topPackages' => Package::withCount(['enquiries' => fn ($q) => $q->notSpam()])->orderByDesc('enquiries_count')->orderBy('display_order')->limit(5)->get(),
        ]);
    }
}
