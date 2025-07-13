<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{public function update(Request $request)
    {
        $user = User::find(Auth::id());
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }
    
        if ($request->filled('username')) {
            $user->username = $request->username;
        }
    
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        
    
        // Save user data, including profile picture path
        $user->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user,
        ], 200);
    }
    

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        // Validate the current password and new password
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Check if the provided current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Current password is incorrect'], 400);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        
        
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
            'user' => $user,
        ], 200);
    }

    public function uploadPicture(Request $request)
{
    // Authenticate user
    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not authenticated',
        ], 401);
    }

    // Validate that the file exists and is an image
    $request->validate([
        'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // Check if file is present and store it
    if ($request->hasFile('profile_picture')) {
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        
        // Update user's profile picture path
        $user->profile_picture = $path;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture uploaded successfully',
            'path' => $path,
            'user' => $user,
            'profile_picture_url' => asset('storage/' . $path),

        ], 200);
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'No profile picture found in request',
        ], 400);
    }
}

public function deleteAccount(Request $request)
{
    $user = Auth::user();

    // Validate that the password is provided
    $request->validate([
        'password' => 'required|string'
    ]);

    // Verify the provided password
    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['status' => 'error', 'message' => 'Incorrect password'], 401);
    }

    // Delete the user account
    $user->delete();

    return response()->json(['status' => 'success', 'message' => 'Account deleted successfully']);
}


}


?>