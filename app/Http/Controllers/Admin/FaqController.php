<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::with('package:id,name')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.faqs.form', [
            'faq' => new Faq(['is_active' => true, 'category' => 'General', 'display_order' => (Faq::max('display_order') ?? 0) + 1]),
            'packages' => Package::ordered()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));

        return redirect()->to(route('admin.faqs.index'))->with('toast', 'FAQ added.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.form', ['faq' => $faq, 'packages' => Package::ordered()->get(['id', 'name'])]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->to(route('admin.faqs.index'))->with('toast', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->to(route('admin.faqs.index'))->with('toast', 'FAQ deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'category' => ['required', Rule::in(Faq::CATEGORIES)],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['display_order'] ??= 0;

        return $data;
    }
}
