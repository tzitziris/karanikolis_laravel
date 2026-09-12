<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 10;

    private const LOCK_SECONDS = 900;

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ],
            [
                'email.required' => 'Γράψτε το email σας.',
                'email.email' => 'Γράψτε ένα έγκυρο email.',
                'password.required' => 'Γράψτε τον κωδικό σας.',
                'password.string' => 'Γράψτε τον κωδικό σας.',
            ],
        );

        $key = $this->throttleKey($request, $credentials['email']);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => $this->lockedMessage(RateLimiter::availableIn($key)),
            ]);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($key, self::LOCK_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Τα στοιχεία σύνδεσης δεν είναι σωστά.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function lockedMessage(int $seconds): string
    {
        $minutes = max(1, (int) ceil($seconds / 60));

        return "Έγιναν πολλές αποτυχημένες προσπάθειες. Δοκιμάστε ξανά σε {$minutes} λεπτά.";
    }

    private function throttleKey(Request $request, string $email): string
    {
        return Str::lower($email).'|'.$request->ip();
    }
}
