<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function show()
    {
        return view('profile.change-password'); // Blade view for changing password
    }

    public function update(Request $request)
    {
        $request->validate([
            'old_password' => ['required'],
            'new_password' => ['required', 'min:8'],
            'confirm_password' => ['required', 'min:8', 'same:new_password'],
        ], [
            'confirm_password.same' => 'The confirm password must match the new password.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->with('error', 'Old password is incorrect.');
        }


        if ($request->new_password == $request->old_password) {
            return back()->with('error', 'password not changed because its the same.');
        }

        // Just assign the new password; Laravel will hash it automatically
        $user->password = $request->new_password;
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }

}
