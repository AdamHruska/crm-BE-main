<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    // Middleware to ensure the user is authenticated
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // Method to get all activities
    public function getActivities()
    {
        $activities = Activity::all();

        return response()->json([
            'activities' => $activities,
            'message' => 'Activities retrieved successfully',
            'status' => 200
        ]);
    }

    // Method to get activities by contact ID
    public function getActivitiesByContact($contactId)
    {
        $activities = Activity::where('contact_id', $contactId)->get();

        return response()->json([
            'activities' => $activities,
            'message' => 'Activities for contact retrieved successfully',
            'status' => 200
        ]);
    }

    // Method to create a new activity
    public function addActivity(Request $request)
    {
        $validatedData = $request->validate([
            'contact_id' => 'required|integer|exists:contacts,id',
            'aktivita' => 'required|string|max:255',
            'datumCas' => 'required|date',
            'koniec' => 'nullable|date',
            'poznamka' => 'nullable|string',
            'volane' => 'nullable|integer',
            'dovolane' => 'nullable|integer',
            'dohodnute' => 'nullable|integer',
            'miesto_stretnutia' => 'nullable|string',
            'online_meeting' => 'nullable|boolean'
        ]);

        $validatedData['created_id'] = auth()->id();

        $activity = Activity::create($validatedData);

        return response()->json([
            'activity' => $activity,
            'message' => 'Activity added successfully',
            'status' => 201
        ]);
    }

    // Method to update an activity
    public function updateActivity(Request $request, $id)
    {
        $validatedData = $request->validate([
            'aktivita' => 'required|string|max:255',
            'datumCas' => 'required|date',
            'koniec' => 'nullable|date',
            'poznamka' => 'nullable|string',
            'volane' => 'nullable|integer',
            'dovolane' => 'nullable|integer',
            'dohodnute' => 'nullable|integer',
            'miesto_stretnutia' => 'nullable|string',
            'online_meeting' => 'nullable|boolean'
        ]);

        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'message' => 'Activity not found',
                'status' => 404
            ], 404);
        }

        $activity->update($validatedData);

        return response()->json([
            'activity' => $activity,
            'message' => 'Activity updated successfully',
            'status' => 200
        ]);
    }

    // Method to delete an activity
    public function deleteActivity($id)
    {
        $activity = Activity::find($id);

        if ($activity) {
            $activity->delete();
            return response()->json([
                'message' => 'Activity deleted successfully',
                'status' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'Activity not found',
                'status' => 404
            ], 404);
        }
    }

    public function getActivitiesDiary()
{
    // Get the logged-in user
    $user = auth()->user();

    // Get the IDs of all contacts belonging to this user
    $contactIds = Contact::where('author_id', $user->id)->pluck('id');

    // Retrieve all activities associated with these contact IDs
    $activities = Activity::whereIn('contact_id', $contactIds)->get();

    // Return the activities in the response
    return response()->json([
        'activities' => $activities,
        'message' => 'Activities retrieved successfully',
        'status' => 200
    ]);
}

public function getActivitiesByUserIds(Request $request)
{
    try {
        // Log the incoming request data
        \Log::info('Incoming user_ids request:', ['data' => $request->all()]);

        // Validate the input with more detailed messages
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ], [
            'user_ids.required' => 'User IDs are required',
            'user_ids.array' => 'User IDs must be an array',
            'user_ids.*.integer' => 'Each user ID must be an integer',
            'user_ids.*.exists' => 'One or more user IDs do not exist in the database',
        ]);

        // Get the IDs of all contacts belonging to the specified users
        $contactIds = Contact::whereIn('author_id', $validated['user_ids'])->pluck('id');

        // Retrieve all activities associated with these contact IDs
        $activities = Activity::whereIn('contact_id', $contactIds)->get();

        // Group activities by user ID
        $groupedActivities = $activities->groupBy(function ($activity) {
            $contact = Contact::find($activity->contact_id);
            return $contact ? $contact->author_id : null;
        });

        return response()->json([
            'activities' => $groupedActivities,
            'message' => 'Activities retrieved successfully',
            'status' => 200
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error:', ['errors' => $e->errors()]);
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors(),
            'status' => 422
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Error in getActivitiesByUserIds:', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'An error occurred while processing your request',
            'error' => config('app.debug') ? $e->getMessage() : 'Server Error',
            'status' => 500
        ], 500);
    }
}

public function getActivityById($id)
{
    $activity = Activity::find($id);

    if (!$activity) {
        return response()->json([
            'message' => 'Activity not found',
            'status' => 404
        ], 404);
    }

    return response()->json([
        'activity' => $activity,
        'message' => 'Activity retrieved successfully',
        'status' => 200
    ]);
}

public function getActivitiesByCreator($creatorId)
{
    // Retrieve activities where created_id matches the specified creatorId
    $activities = Activity::where('created_id', $creatorId)->get();

    // Check if activities exist for the creator
    if ($activities->isEmpty()) {
        return response()->json([
            'message' => 'No activities found for this creator',
            'status' => 404
        ], 404);
    }

    return response()->json([
        'activities' => $activities,
        'message' => 'Activities retrieved successfully',
        'status' => 200
    ]);
}

public function addShareID($id)
{
    try {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        // Cast $id to integer
        $id = (int)$id;
        
        // Use the array casting we defined in the User model
        $sharedUserIds = $user->share_user_id ?? [];

        // Check if the ID already exists in the array
        if (in_array($id, $sharedUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Share ID already added',
            ], 406);
        }

        // Add the new ID to the array
        $sharedUserIds[] = $id;
        
        // Save directly to the array cast property
        $user->share_user_id = $sharedUserIds;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Share ID added successfully',
            'user' => $user,
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in addShareID: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Server error occurred',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

public function setShareIDfromArray($id)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        // Cast $id to integer
        $id = (int)$id;

        // Use the array casting we defined in the User model
        $sharedUserIds = $user->share_user_id ?? [];

        // Check if the ID exists in the array
        if (!in_array($id, $sharedUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Share ID does not exist',
            ], 404);
        }

        // Remove the specific ID from the array
        $sharedUserIds = array_filter($sharedUserIds, fn($shareId) => $shareId != $id);

        // Save directly to the array cast property
        $user->share_user_id = array_values($sharedUserIds); // Re-index the array
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Share ID removed successfully',
            'user' => $user,
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in setShareIDfromArray: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Server error occurred',
            'debug' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}


    
}
