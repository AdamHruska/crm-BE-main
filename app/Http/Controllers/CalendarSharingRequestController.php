<?php

namespace App\Http\Controllers;

use App\Models\CalendarSharingRequest;
use Illuminate\Http\Request;

class CalendarSharingRequestController extends Controller
{
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
        $validatedData = $request->validate([
            'requester_name' => 'required|string|max:255',
            'requester_id' => 'required|exists:users,id',
            'target_user_name' => 'required|string|max:255',
            'target_user_id' => 'required|exists:users,id',
            'type' => 'required|in:view_their_calendar,let_them_view_mine',
            'status' => 'required|in:pending,accepted',
        ]);

        $calendarSharingRequest = CalendarSharingRequest::create($validatedData);

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
}
