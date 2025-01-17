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

    public function letThemViewMineCalendarRequests() {
        $user = Auth::user();
        $userId = $user->id;    
        $requests = CalendarSharingRequest::where('type', 'let_them_view_mine')->where('target_user_id', $userId)->get();

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

    public function addShareIDById($userId)
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

}


