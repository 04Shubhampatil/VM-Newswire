<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Signs in with either the username (the user's name, e.g. "admin") or the email address.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($data['login']);
        $email = str_contains($login, '@')
            ? strtolower($login)
            : User::whereRaw('LOWER(name) = ?', [strtolower($login)])->value('email');

        if (! $email || ! Auth::attempt(['email' => $email, 'password' => $data['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['login' => 'These credentials do not match our records.']);
        }

        if (! $request->user()->isAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages(['login' => 'This account does not have admin access.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out.');
    }
}
