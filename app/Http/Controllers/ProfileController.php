<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */

    public function index()
    {
        //get user
        $user = User::where('email', Auth::user()->email)->first();

        return view('profile.profile', [
            'user' => $user,
        ]); // Create resources/views/profile.blade.php
    }

    public function create()
    {
        $user = Auth::user();

        return view(
            'profile.image.create',
            ['id' => $user->id]
        );
    }

    public function store(Request $request)
    {
        //Validate the uploaded image
        $request->validate([
            'image' => 'required|image|file|mimes:jpeg,png,jpg,gif|max:6144', // Max 6MB
        ]);

        // Store the image in storage/app/public/images
        $path = $request->file('image')->store('images', 'public');

        // If you want to save the path to a database, update the user or other model
        // Example: Assuming the authenticated user uploads the image
        if (auth()->user()) {
            auth()->user()->update(['profile_image' => $path]);
        }
        return redirect()
            ->back()
            ->with('success', 'Image uploaded successfully!');
    }
}
