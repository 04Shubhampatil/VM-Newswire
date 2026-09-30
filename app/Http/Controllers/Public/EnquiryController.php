<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Services\EnquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * Handles both the in-page (fetch, JSON) submission and the no-JS form post (redirect).
     */
    public function store(StoreEnquiryRequest $request, EnquiryService $enquiries): JsonResponse|RedirectResponse
    {
        $packageName = null;

        if ($request->isSpam()) {
            // Honeypot filled: act as if it worked, but store nothing and send nothing.
            Log::info('Enquiry discarded as spam (honeypot)', ['ip' => $request->ip()]);
        } else {
            $packageName = $enquiries->submit($request->validated())->package_name_snapshot;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Thank you. Your enquiry has been received.',
                'package' => $packageName,
            ], 201);
        }

        return redirect()->route('enquiries.thanks')->with('enquiry_submitted', ['package' => $packageName]);
    }

    public function thanks(): View|RedirectResponse
    {
        if (! session()->has('enquiry_submitted')) {
            return redirect()->route('home');
        }

        return view('public.thanks', ['packageName' => session('enquiry_submitted')['package'] ?? null]);
    }
}
