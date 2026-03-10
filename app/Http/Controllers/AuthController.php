<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'form.index');
        }
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
            
            return redirect()->intended(
                $user->isAdmin() ? route('admin.dashboard') : route('form.index')
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
