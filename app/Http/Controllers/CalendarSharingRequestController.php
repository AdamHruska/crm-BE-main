<?php

namespace App\Http\Controllers;

use App\Models\CalendarSharingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CalendarSharingRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');  // Add authentication middleware
    }

    public function viewTheirCalendarRequests() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'view_their_calendar')->where('requester_id', $userId)->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }

    public function viewTheirCalendarRequestsToBeApproved() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'view_their_calendar')->where('target_user_id', $userId)->here('status', 'pending')->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }

    public function letThemViewMineCalendarRequests() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'let_them_view_mine')->where('requester_name', $userId)->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }

    public function letThemViewMineCalendarRequestsSkuska() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'let_them_view_mine')->where('requester_id', $userId)->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }

    public function letThemViewMineCalendarRequestsTabulka() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'let_them_view_mine')->where('target_user_id', $userId)->where('status', 'pending')->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }

    public function letThemViewMineCalendarRequestsForApproval() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'view_their_calendar')->where('target_user_id', $userId)->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }

    public function returnHistory() {
        $user = Auth::user();
        $userId = $user->id;    
       
        $requests = CalendarSharingRequest::where('requester_id', $userId)->orWhere('target_user_id', $userId)->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }

    public function whoSeesMyCalendar() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'let_them_view_mine')->where('requester_id', $userId)->where('status', 'accepted')->get();

        return response()->json([
            'requests' => $requests,
            'message' => $requests->isEmpty() ? 'No requests found' : 'Requests retrieved successfully',
            'status' => 200
        ]);
    }


        /**
     * Display a listing of calendar sharing requests.
     */
    public function index()
    {
        $requests = CalendarSharingRequest::with(['requester', 'targetUser'])->get();
        return response()->json($requests);
    }

    /**
     * Store a newly created calendar sharing request.
     */
    public function store(Request $request)
{
    // Get the logged-in user
    $user = Auth::user();

    // Add requester_name and requester_id dynamically
    $requestData = $request->validate([
        'target_user_name' => 'required|string|max:255',
        'target_user_id' => 'required|exists:users,id',
        'type' => 'required|in:view_their_calendar,let_them_view_mine',
        'status' => 'required|in:pending,accepted',
    ]);

    // Add logged-in user's data
    $requestData['requester_name'] = $user->first_name . ' ' . $user->last_name;
    $requestData['requester_id'] = $user->id;

    // Create the calendar sharing request
    $calendarSharingRequest = CalendarSharingRequest::create($requestData);

    // Return the response
    return response()->json([
        'message' => 'Calendar sharing request created successfully.',
        'data' => $calendarSharingRequest,
    ], 201);
}


    /**
     * Show the details of a specific calendar sharing request.
     */
    public function show($id)
    {
        $request = CalendarSharingRequest::with(['requester', 'targetUser'])->findOrFail($id);
        return response()->json($request);
    }

    /**
     * Update the specified calendar sharing request.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'requester_name' => 'sometimes|string|max:255',
            'requester_id' => 'sometimes|exists:users,id',
            'target_user_name' => 'sometimes|string|max:255',
            'target_user_id' => 'sometimes|exists:users,id',
            'type' => 'sometimes|in:view_their_calendar,let_them_view_mine',
            'status' => 'sometimes|in:pending,accepted',
        ]);

        $calendarSharingRequest = CalendarSharingRequest::findOrFail($id);
        $calendarSharingRequest->update($validatedData);

        return response()->json([
            'message' => 'Calendar sharing request updated successfully.',
            'data' => $calendarSharingRequest,
        ]);
    }

    /**
     * Remove the specified calendar sharing request.
     */
    public function destroy($id)
    {
        $calendarSharingRequest = CalendarSharingRequest::findOrFail($id);
        $calendarSharingRequest->delete();

        return response()->json([
            'message' => 'Calendar sharing request deleted successfully.',
        ]);
    }

    // public function addShareIDById($userId)
    // {
    //     try {
    //         $user = Auth::user();
    
    //         if (!$user) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'User not authenticated',
    //             ], 401);
    //         }
    
    //         $loggedInUserId = (int)$user->id; // Ensure integer type
    //         $targetUser = User::find($userId);
    
    //         if (!$targetUser) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Target user not found',
    //             ], 404);
    //         }
    
    //         // More robust JSON decoding
    //         try {
    //             $sharedUserIds = is_array($targetUser->share_user_id) 
    //                 ? $targetUser->share_user_id 
    //                 : (
    //                     $targetUser->share_user_id 
    //                         ? json_decode($targetUser->share_user_id, true) 
    //                         : []
    //                 );
    
    //             $confirmedShareUserIds = is_array($targetUser->confirmed_share_user_id) 
    //                 ? $targetUser->confirmed_share_user_id 
    //                 : (
    //                     $targetUser->confirmed_share_user_id 
    //                         ? json_decode($targetUser->confirmed_share_user_id, true) 
    //                         : []
    //                 );
    //         } catch (\JsonException $e) {
    //             \Log::error('JSON decode error:', ['error' => $e->getMessage()]);
    //             $sharedUserIds = [];
    //             $confirmedShareUserIds = [];
    //         }
    
    //         // Ensure arrays contain integers
    //         $sharedUserIds = array_map('intval', (array)$sharedUserIds);
    //         $confirmedShareUserIds = array_map('intval', (array)$confirmedShareUserIds);
    
    //         // Debug output
    //         \Log::info('Checking values:', [
    //             'loggedInUserId' => $loggedInUserId,
    //             'sharedUserIds' => $sharedUserIds,
    //             'confirmedShareUserIds' => $confirmedShareUserIds
    //         ]);
    
    //         if (in_array($loggedInUserId, $sharedUserIds, strict: true)) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Logged-in user already added to share_user_id',
    //             ], 406);
    //         }
    
    //         if (in_array($loggedInUserId, $confirmedShareUserIds, strict: true)) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Logged-in user already confirmed for target user',
    //             ], 406);
    //         }
    
    //         // Add the logged-in user's ID
    //         $sharedUserIds[] = $loggedInUserId;
    //         $confirmedShareUserIds[] = $loggedInUserId;
    
    //         // Save back as JSON with error checking
    //         try {
    //             $targetUser->share_user_id = json_encode($sharedUserIds, JSON_THROW_ON_ERROR);
    //             $targetUser->confirmed_share_user_id = json_encode($confirmedShareUserIds, JSON_THROW_ON_ERROR);
    //         } catch (\JsonException $e) {
    //             \Log::error('JSON encode error:', ['error' => $e->getMessage()]);
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Failed to encode user data',
    //             ], 500);
    //         }
    
    //         if (!$targetUser->save()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Failed to save updated user data',
    //             ], 500);
    //         }
    
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Logged-in user added successfully',
    //             'user' => $targetUser,
    //         ]);
    
    //     } catch (\Exception $e) {
    //         \Log::error('Error in addShareIDById: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Server error occurred',
    //             'debug' => config('app.debug') ? $e->getMessage() : null
    //         ], 500);
    //     }
    // }
    
//     public function addShareIDById($userId)
// {
//     try {
//         $user = Auth::user();

//         if (!$user) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'User not authenticated',
//             ], 401);
//         }

//         $loggedInUserId = (int)$user->id;
//         $targetUser = User::find($userId);

//         if (!$targetUser) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Target user not found',
//             ], 404);
//         }

//         // Handle empty or string "[]" cases
//         $sharedUserIds = $targetUser->share_user_id;
//         if ($sharedUserIds === "[]" || $sharedUserIds === null || $sharedUserIds === "") {
//             $sharedUserIds = [];
//         } else if (is_string($sharedUserIds)) {
//             $sharedUserIds = json_decode($sharedUserIds, true) ?? [];
//         }

//         $confirmedShareUserIds = $targetUser->confirmed_share_user_id;
//         if ($confirmedShareUserIds === "[]" || $confirmedShareUserIds === null || $confirmedShareUserIds === "") {
//             $confirmedShareUserIds = [];
//         } else if (is_string($confirmedShareUserIds)) {
//             $confirmedShareUserIds = json_decode($confirmedShareUserIds, true) ?? [];
//         }

//         // Ensure arrays contain integers
//         $sharedUserIds = array_map('intval', (array)$sharedUserIds);
//         $confirmedShareUserIds = array_map('intval', (array)$confirmedShareUserIds);

//         if (in_array($loggedInUserId, $sharedUserIds, strict: true)) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Logged-in user already added to share_user_id',
//             ], 406);
//         }

//         if (in_array($loggedInUserId, $confirmedShareUserIds, strict: true)) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Logged-in user already confirmed for target user',
//             ], 406);
//         }

//         // Add the logged-in user's ID
//         $sharedUserIds[] = $loggedInUserId;
//         $confirmedShareUserIds[] = $loggedInUserId;

//         // Save back as JSON, but handle empty arrays specially
//         $targetUser->share_user_id = empty($sharedUserIds) ? null : json_encode($sharedUserIds);
//         $targetUser->confirmed_share_user_id = empty($confirmedShareUserIds) ? null : json_encode($confirmedShareUserIds);

//         if (!$targetUser->save()) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Failed to save updated user data',
//             ], 500);
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Logged-in user added successfully',
//             'user' => $targetUser,
//         ]);

//     } catch (\Exception $e) {
//         \Log::error('Error in addShareIDById: ' . $e->getMessage());
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Server error occurred',
//             'debug' => config('app.debug') ? $e->getMessage() : null
//         ], 500);
//     }
// }

public function addShareIDById($userId, $requestId)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        // Get the ID of the logged-in user
        $loggedInUserId = $user->id;

        // Find the target user by ID
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Target user not found',
            ], 404);
        }

        // Fetch the current share_user_id and confirmed_share_user_id of the target user
        $sharedUserIds = $targetUser->share_user_id ?? [];
        $confirmedShareUserIds = $targetUser->confirmed_share_user_id ?? [];

        // Check if the logged-in user's ID already exists in share_user_id
        if (in_array($loggedInUserId, $sharedUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logged-in user already added to share_user_id',
            ], 406);
        }

        // Check if the logged-in user's ID already exists in confirmed_share_user_id
        if (in_array($loggedInUserId, $confirmedShareUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logged-in user already confirmed for target user',
            ], 406);
        }

        // Add the logged-in user's ID to both share_user_id and confirmed_share_user_id
        $sharedUserIds[] = $loggedInUserId;
        $confirmedShareUserIds[] = $loggedInUserId;

        $targetUser->share_user_id = $sharedUserIds;
        $targetUser->confirmed_share_user_id = $confirmedShareUserIds;

        // Save the updated target user
        $targetUser->save();

        $request = CalendarSharingRequest::find($requestId);

        if (!$request) {
            return response()->json([
                'status' => 'error',
                'message' => 'Calendar sharing request not found',
            ], 404);
        }

        $request->status = 'accepted';
        $request->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged-in user added to share_user_id and confirmed_share_user_id successfully',
            'user' => $targetUser,
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in addLoggedInUserToShareIds: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Server error occurred',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

public function addShareIDByIdTable($userId, $requestId)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        // Convert $userId to integer
        $userId = intval($userId);

        // Get the ID of the logged-in user
        $loggedInUserId = $user->id;

        // Find the target user by ID (the logged-in user)
        $targetUser = $user;  // The logged-in user is the target user

        // Fetch the current share_user_id and confirmed_share_user_id of the logged-in user
        $sharedUserIds = $targetUser->share_user_id ?? [];
        $confirmedShareUserIds = $targetUser->confirmed_share_user_id ?? [];

        // Check if the provided $userId already exists in share_user_id
        if (in_array($userId, $sharedUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already added to share_user_id',
            ], 406);
        }

        // Check if the provided $userId already exists in confirmed_share_user_id
        if (in_array($userId, $confirmedShareUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already confirmed for the logged-in user',
            ], 406);
        }

        // Add the provided $userId to both share_user_id and confirmed_share_user_id
        $sharedUserIds[] = $userId;
        $confirmedShareUserIds[] = $userId;

        $targetUser->share_user_id = $sharedUserIds;
        $targetUser->confirmed_share_user_id = $confirmedShareUserIds;

        // Save the updated logged-in user (target user)
        $targetUser->save();

        $request = CalendarSharingRequest::find($requestId);

        if (!$request) {
            return response()->json([
                'status' => 'error',
                'message' => 'Calendar sharing request not found',
            ], 404);
        }

        $request->status = 'accepted';
        $request->save();


        return response()->json([
            'status' => 'success',
            'message' => 'User added to share_user_id and confirmed_share_user_id successfully',
            'user' => $targetUser,
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in addShareIDByIdTable: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Server error occurred',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}


}



