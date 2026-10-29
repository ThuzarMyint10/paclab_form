<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /* ---------- Customer ---------- */

    public function showCustomerLogin()
    {
        return view('auth.login', ['area' => 'customer']);
    }

    public function customerLogin(Request $request)
    {
        return $this->attempt($request, 'customer');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'company_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'email.unique' => 'An account with this email already exists. Use "Forgot password" to set a password.',
        ]);

        $company = Company::matchByName($data['company_name']);

        $user = User::create($data + ['role' => 'customer', 'company_id' => $company?->id]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard')->with('success', 'Welcome! Your customer account is ready.');
    }

    /* ---------- Staff ---------- */

    public function showAdminLogin()
    {
        return view('auth.login', ['area' => 'admin']);
    }

    public function adminLogin(Request $request)
    {
        return $this->attempt($request, 'admin');
    }

    /* ---------- Shared ---------- */

    protected function attempt(Request $request, string $area)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::user();

        // Keep staff and customers in their own areas
        if ($area === 'admin' && ! $user->isStaff()) {
            Auth::logout();

            return back()->withInput($request->only('email'))->withErrors(['email' => 'This login is for Pacific Lab staff only. Customers please use the customer login.']);
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return $user->isStaff()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        $wasStaff = $request->user()?->isStaff();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($wasStaff ? 'admin.login' : 'home');
    }

    /* ---------- Password reset / first-time set-up ---------- */

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email'));

        // Same answer whether or not the address exists (no account fishing)
        return back()->with('success', 'If an account exists for that email, a password link has been sent.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
        }

        $user = User::where('email', $request->email)->first();

        return redirect()->route($user && $user->isStaff() ? 'admin.login' : 'login')
            ->with('success', 'Your password has been set. Please log in.');
    }
}
