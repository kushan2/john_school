<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    
    
// ── Register ─────────────────────────────────────────────────────────────────

public function showRegistrationForm()
    {
        return view('auth.register');
    }

public function register(Request $request)
    {

    $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            // Must be a school-issued .edu address (allows sub-domains such as
            // student@mail.college.edu).
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/@[^@\s]+\.edu$/i'],
            'campus'   => ['required', 'string', Rule::in(config('campuses.list'))],
            'password' => ['required', 'confirmed', PasswordRule::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ], [
            'email.regex' => 'You must register with your school-issued .edu email address.',
            'campus.in'   => 'Please select your campus from the list.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'campus'   => $validated['campus'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('pages.dashboard')
                         ->with('success', 'Account created successfully!');
    }
    
    
    
// ── Login ─────────────────────────────────────────────────────────────────
public function showLogin()
    {
        return view('auth.login');
    }


public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('pages.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    
public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    
    
    
// ── Forgot Password ───────────────────────────────────────────────────────
public function showForgot()
    {
        return view('auth.email');
    }

public function sendReset(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    
    


// ── Reset Password ────────────────────────────────────────────────────────
public function showReset(Request $request, string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->email]);
    }

public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successfully. Please log in.')
            : back()->withErrors(['email' => __($status)]);
}
}
