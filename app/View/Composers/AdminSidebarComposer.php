<?php

namespace App\View\Composers;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\View\View;

class AdminSidebarComposer
{
    public function compose(View $view): void
    {
        $view->with('newEnquiries', Enquiry::where('status', EnquiryStatus::New)->count());
    }
}
