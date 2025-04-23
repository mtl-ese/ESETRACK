<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOnlineTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function create()
    {
        if (auth()->check()) {
            // Redirect authenticated users to the dashboard
            return redirect()->route('dashboard');
        }

        return view("login");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Get the user
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()
                ->with(
                    'error',
                    'Invalid credentials. Please try again.'
                )->withInput();
        }

        // Check if account is activated
        if (!$user->isActivated) {
            return back()
                ->with(
                    'error',
                    'Account not active. Please contact your administrator.'
                )->withInput();
        }

        // Attempt to log in the user
        if (
            Auth::attempt([
                'email' => $request->input('email'),
                'password' => $request->input('password')
            ])
        ) {
            $user->update(['last_login_at' => now(), 'is_active' => true]);
            // Authentication passed, redirect to the intended page

            return redirect()->route('dashboard')
                ->with('success', 'You have successfully logged in!')
                ->withHeaders([
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, proxy-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]);
        } else {
            // Authentication failed, redirect back with an error message
            return back()->with(
                'error',
                'Invalid credentials. Please try again.'
            )->withInput();
        }
    }

    public function destroy(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        if ($user) {
            // Store the current time in last_seen_at
            $user->last_seen_at = now();

            // Calculate the total online time
            $lastLoginAt = $user->last_login_at;
            $currentTime = now();
            $onlineTime = $lastLoginAt->diffInSeconds($currentTime);

            // Store the online time in the user_online_times table
            $userOnlineTime = UserOnlineTime::firstOrNew([
                'user_id' => $user->id,
                'date' => $currentTime->toDateString(),
            ]);

            $userOnlineTime->online_time += $onlineTime;
            $userOnlineTime->save();

            // Set is_active to false
            $user->is_active = false;

            // Save the user
            $user->save();
        }

        // Log the user out
        Auth::logout();

        // Invalidate the session to prevent session fixation attacks
        $request->session()->invalidate();

        // Regenerate the session to prevent session hijacking
        $request->session()->regenerateToken();

        // Redirect the user to the login page or home page after logging out
        return redirect()->route('login'); // Or another route like 'home'
    }
}
