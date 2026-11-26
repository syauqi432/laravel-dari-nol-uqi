<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\InitializeInvestmentOwner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        $ownerAccountExists = User::query()->exists();

        if (! $ownerAccountExists && app()->environment('local', 'testing')) {
            return redirect()->route('owner.setup');
        }

        return view('auth.login');
    }

    public function showRegistration(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ]);
        $user = User::create($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('market.overview')->with('success', 'Akun berhasil dibuat. Data portofolio Anda bersifat privat.');
    }

    public function login(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);
        $rateLimitKey = Str::transliterate(Str::lower($validated['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.RateLimiter::availableIn($rateLimitKey).' detik.',
            ]);
        }

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            RateLimiter::hit($rateLimitKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($rateLimitKey);
        $request->session()->regenerate();

        return redirect()->intended(route('market.overview'));
    }

    public function showOwnerSetup(): View|RedirectResponse
    {
        $this->ensureOwnerSetupIsAvailable();

        $legacyRecords = DB::table('sahams')->whereNull('user_id')->count();

        return view('auth.setup', compact('legacyRecords'));
    }

    public function createOwner(Request $request, InitializeInvestmentOwner $initializeOwner): RedirectResponse
    {
        $this->ensureOwnerSetupIsAvailable();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ]);
        $owner = $initializeOwner->create($validated);

        Auth::login($owner);
        $request->session()->regenerate();

        return redirect()->route('market.overview');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function ensureOwnerSetupIsAvailable(): void
    {
        abort_unless(app()->environment('local', 'testing'), 404);
        abort_if(User::query()->exists(), 404);
    }
}
