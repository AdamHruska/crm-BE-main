<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;

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
            'poznamka' => 'nullable|string',
            'volane' => 'required|integer',
            'dovolane' => 'required|integer',
            'dohodnute' => 'required|integer'
        ]);

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
            'poznamka' => 'nullable|string',
            'volane' => 'required|integer',
            'dovolane' => 'required|integer',
            'dohodnute' => 'required|integer'
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
}
