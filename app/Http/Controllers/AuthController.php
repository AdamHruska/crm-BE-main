<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Error;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zlé meno alebo heslo',
            ], 401);
        }
        $user = Auth::user();

        // if ($user->email_verified_at == null) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Email not verified',
        //     ], 401);
        // }

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 406);
        }
        
        $user = User::create([
            // 'profile_picture' => $profile_image,
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zlé meno alebo heslo',
            ], 401);
        }
        #event(new Registered($user));

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function addShareID($id) {
        $user = Auth::user();
    
        // Decode the JSON string into an array, or initialize as an empty array if null
        $sharedUserIds = json_decode($user->share_user_id, true) ?? [];
    
        // Check if the ID already exists in the array
        if (in_array($id, $sharedUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Share ID already added',
            ], 406);
        } else {
            // Add the new ID to the array
            $sharedUserIds[] = $id;
    
            // Re-encode the array back to JSON
            $user->share_user_id = json_encode(array_values($sharedUserIds));
            $user->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Share ID added successfully',
                'user' => $user,
            ]);
        }
    }
    
    public function setShareIDfromArray($id) {
        $user = Auth::user();
        
        // Decode the JSON string into an array, or use an empty array if null
        $sharedUserIds = json_decode($user->share_user_id, true) ?? [];
    
        // Remove the specific ID from the array
        $sharedUserIds = array_filter($sharedUserIds, fn($shareId) => $shareId != $id);
        
        // Re-index the array
        $user->share_user_id = json_encode(array_values($sharedUserIds)); 
    
        // Save the user model
        $user->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Share ID(s) removed successfully',
            'user' => $user,
        ]);
    }

    // public function setShareIDfromArray($id) {
    //     try {
    //         $user = Auth::user();
            
    //         // Add logging to debug
    //         \Log::info('Current share_user_id:', ['data' => $user->share_user_id]);
            
    //         // Decode the JSON string into an array, or use an empty array if null
    //         $sharedUserIds = json_decode($user->share_user_id, true);
            
    //         if ($sharedUserIds === null) {
    //             $sharedUserIds = [];
    //         }
            
    //         // Remove the specific ID from the array
    //         $sharedUserIds = array_filter($sharedUserIds, function($shareId) use ($id) {
    //             return $shareId != $id;
    //         });
            
    //         // Re-index the array
    //         $sharedUserIds = array_values($sharedUserIds);
            
    //         // Convert back to JSON
    //         $user->share_user_id = json_encode($sharedUserIds);
            
    //         // Save the user model
    //         $user->save();
            
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Share ID(s) removed successfully',
    //             'user' => $user,
    //             'removed_id' => $id
    //         ]);
            
    //     } catch (\Exception $e) {
    //         \Log::error('Error in setShareIDfromArray:', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
            
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred while processing your request',
    //             'debug_message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

//     public function confirmShareRequest(Request $request)
// {
//     $user = auth()->user();
//     $shareId = $request->input('share_id'); // ID to be confirmed

//     if (!$shareId || !in_array($shareId, $user->share_user_id)) {
//         return response()->json(['error' => 'Invalid share ID'], 400);
//     }

//     $confirmedShareIds = $user->confirmed_share_ids ?? [];
//     if (!in_array($shareId, $confirmedShareIds)) {
//         $confirmedShareIds[] = $shareId;
//         $user->confirmed_share_ids = $confirmedShareIds;
//         $user->save();
//     }

//     return response()->json(['message' => 'Share request confirmed']);
// }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function gerUser()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }
}
