<?php

namespace App\Http\Controllers;

use App\Models\CallList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CallListController extends Controller
{
    /**
     * Display a listing of the call lists.
     */
    public function index()
    {
        $user = auth()->user();

        $callLists = CallList::where('author_id', $user->id)->get();
        return response()->json($callLists);
    }

    /**
     * Store a newly created call list.
     * Expects an array of contact IDs in the request.
     */
    public function store(Request $request)
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'author_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'integer|exists:contacts,id', // Ensure each ID exists in contacts
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create the call list
        $callList = CallList::create([
            'author_id' => $request->input('author_id'),
            'name' => $request->input('name'),
            'contact_ids' => json_encode($request->contact_ids), 
        ]);

        return response()->json($callList, 201);
    }

    /**
     * Display the specified call list.
     */
    public function show($id)
    {
        $callList = CallList::find($id);

        if (!$callList) {
            return response()->json(['error' => 'Call list not found'], 404);
        }

        return response()->json($callList);
    }

    /**
     * Update the specified call list.
     */
    public function update(Request $request, $id)
    {
        $callList = CallList::find($id);

        if (!$callList) {
            return response()->json(['error' => 'Call list not found'], 404);
        }

        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'contact_ids' => 'sometimes|required|array',
            // 'contact_ids.*' => 'integer|exists:contacts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update the call list
        $callList->update([
            'name' => $request->input('name', $callList->name),
            'contact_ids' => json_encode($request->input('contact_ids', json_decode($callList->contact_ids))),
        ]);

        return response()->json($callList);
    }

    /**
     * Remove the specified call list.
     */
    public function destroy($id)
    {
        $callList = CallList::find($id);

        if (!$callList) {
            return response()->json(['error' => 'Call list not found'], 404);
        }

        $callList->delete();

        return response()->json(['message' => 'Call list deleted successfully']);
    }
}
