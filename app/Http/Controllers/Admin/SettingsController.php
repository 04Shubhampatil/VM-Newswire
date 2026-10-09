<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(SiteSettings $settings): View
    {
        return view('admin.settings', ['settings' => $settings]);
    }

    public function update(Request $request, SiteSettings $settings): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'company_email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:300'],
            'admin_notification_email' => ['nullable', 'email', 'max:190'],
            'network_size_label' => ['required', 'string', 'max:12'],
            'social_linkedin' => ['nullable', 'url:https', 'max:255'],
            'social_x' => ['nullable', 'url:https', 'max:255'],
            'social_facebook' => ['nullable', 'url:https', 'max:255'],
            'social_instagram' => ['nullable', 'url:https', 'max:255'],
            'social_youtube' => ['nullable', 'url:https', 'max:255'],
        ]);

        $settings->set($data);

        return back()->with('toast', 'Settings saved.');
    }
}
