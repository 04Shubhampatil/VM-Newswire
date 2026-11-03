<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Services\EnquiryService;
use App\Services\SpamDetector;
use App\Support\Altcha;
use App\Support\Captcha;
use App\Support\ImageCaptcha;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * Handles both the in-page (fetch, JSON) submission and the no-JS form post (redirect).
     */
    public function store(StoreEnquiryRequest $request, EnquiryService $enquiries, SpamDetector $detector): JsonResponse|RedirectResponse
    {
        $context = $request->spamContext();
        $assessment = $detector->assess($request->validated(), $context);

        // Every valid enquiry is saved. Spam is saved with status Spam and sends no emails; the visitor
        // sees the same success either way, so spammers learn nothing from the response.
        $enquiry = $enquiries->submit($request->validated(), $assessment, $context['ip']);
        $packageName = $enquiry->package_name_snapshot;

        if ($assessment->isSpam()) {
            Log::info('Enquiry saved as spam', ['id' => $enquiry->id, 'score' => $assessment->score, 'rules' => $assessment->rules(), 'ip' => $context['ip']]);
        }
        $request->session()->forget(StoreEnquiryRequest::STARTED_AT);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Thank you. Your enquiry has been received.',
                'package' => $packageName,
            ], 201);
        }

        return redirect()->route('enquiries.thanks')->with('enquiry_submitted', ['package' => $packageName]);
    }

    /**
     * A fresh CSRF token for the enquiry form. Public pages are served from the page cache, so the token in
     * their HTML belongs to someone else's session; the form swaps it for this one when the visitor starts typing.
     * The time is remembered so submissions made faster than a person could type are discarded.
     */
    public function token(Request $request): JsonResponse
    {
        $startedAt = $request->session()->get(StoreEnquiryRequest::STARTED_AT);
        if (! is_int($startedAt) || $startedAt < now()->subHours(2)->timestamp) {
            $request->session()->put(StoreEnquiryRequest::STARTED_AT, now()->timestamp);
        }

        return response()->json(['token' => csrf_token()])
            ->header('Cache-Control', 'no-store, private');
    }

    /** A new picture captcha for one form on the page (each request draws a new word; never cached). */
    public function captcha(string $form, ImageCaptcha $captcha): Response
    {
        abort_unless(Captcha::driver() === 'image' && ImageCaptcha::formKey($form), 404);

        return response($captcha->issue($form), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
        ]);
    }

    /** A fresh ALTCHA puzzle for the "verify you are human" check (never cached). */
    public function challenge(): JsonResponse
    {
        abort_unless(Altcha::enabled(), 404);

        return response()->json(Altcha::challenge())->header('Cache-Control', 'no-store, private');
    }

    public function thanks(): View|RedirectResponse
    {
        if (! session()->has('enquiry_submitted')) {
            return redirect()->route('home');
        }

        return view('public.thanks', ['packageName' => session('enquiry_submitted')['package'] ?? null]);
    }
}
