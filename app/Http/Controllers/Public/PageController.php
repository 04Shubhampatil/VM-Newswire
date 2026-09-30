<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('public.about');
    }

    public function contact(Request $request): View
    {
        $packages = Package::active()->ordered()->get(['id', 'name', 'slug', 'price', 'currency']);

        return view('public.contact', [
            'packages' => $packages,
            // /contact?package=slug pre-selects a package (links from package cards etc.)
            'selected' => $packages->firstWhere('slug', (string) $request->query('package')),
            'faqs' => Faq::active()->whereNull('package_id')->where('category', 'Enquiries')->ordered()->limit(4)->get(),
        ]);
    }

    public function faq(): View
    {
        $faqs = Faq::active()->whereNull('package_id')->ordered()->get();

        return view('public.faq', [
            'faqs' => $faqs,
            'categories' => $faqs->pluck('category')->unique()->values(),
        ]);
    }

    public function privacy(): View
    {
        return view('public.legal', ['title' => 'Privacy Policy', 'key' => 'privacy']);
    }

    public function terms(): View
    {
        return view('public.legal', ['title' => 'Terms & Conditions', 'key' => 'terms']);
    }
}
