<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Contact;

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
    // Extract user_ids from the request
    $userIds = $request->input('user_ids'); // Expect an array

    // Validate the input
    $request->validate([
        'user_ids' => 'required|array',          // Ensure user_ids is an array
        'user_ids.*' => 'integer|exists:users,id', // Ensure each ID is an integer and exists in the users table
    ]);

    // Get the IDs of all contacts belonging to the specified users
    $contactIds = Contact::whereIn('author_id', $userIds)->pluck('id');

    // Retrieve all activities associated with these contact IDs
    $activities = Activity::whereIn('contact_id', $contactIds)->get();

    // Group activities by user ID
    $groupedActivities = $activities->groupBy(function ($activity) {
        $contact = Contact::find($activity->contact_id);
        return $contact ? $contact->author_id : null; // Return null if contact not found
    });

    // Return the activities in the response
    return response()->json([
        'activities' => $groupedActivities,
        'message' => 'Activities retrieved successfully',
        'status' => 200
    ]);
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
    
}
