<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            $homeRoute = $this->resolveHomeRoute($user);
            $intendedUrl = (string) $request->session()->get('url.intended', '');

            // Never send non-admin users to admin area from stale intended URLs.
            if (!$user->isAdmin() && Str::contains($intendedUrl, '/admin')) {
                $request->session()->forget('url.intended');
                return redirect()->route($homeRoute);
            }
            
            return redirect()->intended(
                route($homeRoute)
            );
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are invalid.'],
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    /**
     * Show registration form (public)
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration (create user account by admin)
     */
    public function register(Request $request)
    {
        // Only admin can create users via form
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'is_admin' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $validated['is_admin'] ?? false,
            'created_by_admin' => true,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Resolve home route by role.
     */
    private function resolveHomeRoute(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin.dashboard';
        }

        if ($user->hasRole('team')) {
            return 'team.dashboard';
        }

        if (Team::query()->whereRaw('LOWER(email) = ?', [strtolower((string) $user->email)])->exists()) {
            return 'team.dashboard';
        }

        if ($user->hasRole('player')) {
            return 'player.profile';
        }

        if (Participant::query()->whereRaw('LOWER(email) = ?', [strtolower((string) $user->email)])->exists()) {
            return 'player.profile';
        }

        return 'form.index';
    }

    // /**
    //  * Create first admin user (only available if no users exist)
    //  */
    // public function createFirstAdmin(Request $request)
    // {
    //     if (User::count() > 0) {
    //         return response()->json(['error' => 'Users already exist'], 403);
    //     }

    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required|min:8|confirmed',
    //     ]);

    //     $admin = User::create([
    //         'name' => $validated['name'],
    //         'email' => $validated['email'],
    //         'password' => Hash::make($validated['password']),
    //         'is_admin' => true,
    //         'created_by_admin' => false,
    //     ]);

    //     Auth::login($admin);
    //     return redirect()->route('admin.dashboard')->with('status', 'Admin account created successfully!');
    // }
}
