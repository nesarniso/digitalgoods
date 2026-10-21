<?php

namespace App\Http\Controllers;

use App\Domain\Auth\Services\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class CustomerAuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function login()
    {
        return view('storefront.auth.login');
    }

    public function authenticate(LoginRequest $request)
    {
        $user = $this->auth->attemptPasswordLogin($request->string('email'), $request->string('password'), $request);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('account.index'))->with('success', 'Welcome back.');
    }

    public function register()
    {
        return view('storefront.auth.register');
    }

    public function forgotPassword()
    {
        return view('storefront.auth.forgot-password');
    }

    public function sendPasswordResetCode(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $this->auth->issuePasswordResetCode($data['email']);

        return redirect()->route('account.password.reset')
            ->with('email', strtolower(trim($data['email'])))
            ->with('success', 'If an account exists for that email, a 4-digit reset code has been sent.');
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:4'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);
        $this->auth->resetPasswordWithCode($data['email'], $data['code'], $data['password']);

        return redirect()->route('account.login')->with('success', 'Password reset successfully. You can now sign in.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $user = $this->auth->registerCustomer($data);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.index')->with('success', 'Account created successfully.');
    }

    public function logout(Request $request)
    {
        $this->auth->logout($request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function createCheckoutSession(Request $request)
    {
        $token = $request->bearerToken();
        $accessToken = $token ? PersonalAccessToken::findToken($token) : null;

        abort_unless($accessToken && $accessToken->tokenable instanceof User, 401);

        Auth::login($accessToken->tokenable);
        $request->session()->regenerate();

        return response()->json(['success' => true]);
    }
}
