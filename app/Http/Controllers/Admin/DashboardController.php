<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmailDeliveryStatus;
use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Enquiry;
use App\Models\MediaOutlet;
use App\Models\Package;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'metrics' => [
                'total' => Enquiry::count(),
                'new' => Enquiry::where('status', EnquiryStatus::New)->count(),
                'last30' => Enquiry::where('created_at', '>=', now()->subDays(30))->count(),
                'packages' => Package::active()->count(),
                'outlets' => MediaOutlet::active()->count(),
            ],
            'failedEmails' => EmailLog::where('delivery_status', EmailDeliveryStatus::Failed)->count(),
            'recent' => Enquiry::with('package:id,name')->latest()->limit(8)->get(),
            'packagesWithoutReport' => Package::active()->doesntHave('sampleReports')->pluck('name'),
        ]);
    }
}
