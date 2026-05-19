<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Events\UserStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function loginStore(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->email;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([
            $field => $login,
            'password' => $request->password,
        ])) {
            $request->session()->regenerate();

            $user = Auth::user();

            $user->update([
                'is_online' => true,
                'last_seen_at' => now(),
            ]);

            broadcast(new UserStatusChanged($user))->toOthers();

            return redirect()->route('chat.index');
        }

        return back()
            ->withErrors([
                'email' => 'Email/nomor HP atau password salah.',
            ])
            ->onlyInput('email');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function registerStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        Auth::login($user);

        broadcast(new UserStatusChanged($user))->toOthers();

        return redirect()->route('chat.index');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            $user->update([
                'is_online' => false,
                'last_seen_at' => now(),
            ]);

            broadcast(new UserStatusChanged($user))->toOthers();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}