<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return view('users.index', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'DOB' => 'required|date|before:today',
        ]);

        //create a new user
        User::create($validated);

        return redirect()
            ->route('usersIndex')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::where('id', $id)->first();

        return view('users.show', [
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function activate(string $id)
    {
        $user = User::where('id', $id)->first();
        $user->update(['isActivated' => true]);

        return redirect()
            ->back()
            ->with('success', $user->first_name.' '.$user->last_name. ' activated successfully.');
    }
    public function deactivate(string $id)
    {
        $user = User::where('id', $id)->first();
        $user->update(['isActivated' => false]);

        return redirect()
            ->back()
            ->with('success', $user->first_name.' '.$user->last_name. ' deactivated successfully.');
    }
    public function resetPassword(string $id)
    {
        $user = User::where('id', $id)->first();
        $user->update(['password
        ' => 'password']);

        return redirect()
            ->back()
            ->with('success', 'Password reset successfully.');
    }
    public function makeAdmin(string $id)
    {
        $user = User::where('id', $id)->first();
        $user->update(['isAdmin' => true]);

        return redirect()
            ->back()
            ->with('success', $user->first_name.' '.$user->last_name. ' is now an admin.');
    }
    public function revokeAdmin(string $id)
    {
        $user = User::where('id', $id)->first();
        $user->update(['isAdmin' => false]);

        return redirect()
            ->back()
            ->with('success', 'Admin privileges revoked.');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|max:255'
        ]);

        $users = User::where('first_name', 'like', "%{$validated['search']}%")
            ->orWhere('last_name', 'like', "%{$validated['search']}%")
            ->orWhere('email', 'like', "%{$validated['search']}%")
            ->get();

        if (!$users->count()) {
            return redirect()
                ->route('usersIndex')
                ->with('error', 'No users found.')
                ->withInput();
        }

        return view('users.index', [
            'users' => $users,
            [
                'search' => $validated['search']
            ]
        ]);
    }
}
