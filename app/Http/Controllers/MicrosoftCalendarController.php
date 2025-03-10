<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\MicrosoftAuth;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class MicrosoftCalendarController extends Controller
{

//     public function callbackAzure(Request $request)
// {
//     // Log the full request
//     Log::info("Received request: " . $request->fullUrl());

//     // Log query parameters
//     Log::info("Query parameters: " . json_encode($request->query()));

//     $code = $request->query('code');

//     if (!$code) {
//         return response()->json(['error' => 'Authorization code not provided'], 400);
//     }

//     Log::info("Client ID: " . env('AZURE_CLIENT_ID'));
//     Log::info("Client Secret: " . env('AZURE_CLIENT_SECRET')); // Don't log secret in production!
//     Log::info("Redirect URI: " . env('AZURE_REDIRECT_URI'));


//     $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
//         'client_id' => env('AZURE_CLIENT_ID'),
//         'client_secret' => env('AZURE_CLIENT_SECRET'),
//         'redirect_uri' => env('AZURE_REDIRECT_URI'),
//         'grant_type' => 'authorization_code',
//         'code' => $code,
//         'scope' => 'offline_access Calendars.Read Calendars.Read.Shared OnlineMeetings.Read',
//     ]);

//     if ($response->failed()) {
//         return response()->json([
//             'error' => 'Failed to retrieve access token',
//             'details' => $response->json()
//         ], 400);
//     }

//     $tokens = $response->json();
//     \Log::info('Microsoft access token in session:', ['token' => $tokens]);

//     session(['microsoft_access_token' => $tokens['access_token']]);

//     return redirect()->to(env('FRONTEND_URL') . '/calendar?status=success');
// }

public function callbackAzure(Request $request)
{
    $code = $request->query('code');

    if (!$code) {
        return response()->json(['error' => 'Authorization code not provided'], 400);
    }

    // Request the access token from Azure
    $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect_uri' => env('AZURE_REDIRECT_URI'),
        'grant_type' => 'authorization_code',
        'code' => $code,
        'scope' => 'offline_access Calendars.Read Calendars.Read.Shared OnlineMeetings.Read',
    ]);

    if ($response->failed()) {
        Log::error('Failed to retrieve Microsoft token: ' . json_encode($response->json()));
        return response()->json(['error' => 'Failed to retrieve access token', 'details' => $response->json()], 400);
    }

    $tokens = $response->json(); // Get tokens from the response
    
    // Add debug logging
    Log::info('Microsoft tokens received', ['access_token_length' => strlen($tokens['access_token'] ?? 'none')]);
    
    // Create a cookie with appropriate settings for cross-domain use on Vercel
    $cookie = cookie('microsoft_access_token', $tokens['access_token'], 60, '/', null, null, false);
    
    // Return response with cookie
    return redirect()->to(env('FRONTEND_URL') . '/calendar?status=success')->withCookie($cookie);
}

public function getEvents(Request $request)
{
    // Get the access token from the cookie
    $accessToken = $request->cookie('microsoft_access_token');

    // Add debugging
    Log::info('Retrieving Microsoft access token from cookie', [
        'token_exists' => !empty($accessToken),
        'token_length' => $accessToken ? strlen($accessToken) : 0,
        'cookies' => $request->cookies->all() // Log all available cookies
    ]);

    if (!$accessToken) {
        return response()->json(['error' => 'Not authenticated with Microsoft', 'debug' => 'No token in cookie'], 401);
    }


    // Fetch events from Microsoft Graph API
    $startDateTime = date('Y-m-d\TH:i:s\Z', strtotime('-6 months'));
    $endDateTime = date('Y-m-d\TH:i:s\Z', strtotime('+6 months'));

    $eventsResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
        'Accept' => 'application/json',
        'Prefer' => 'outlook.timezone="UTC"'
    ])->get('https://graph.microsoft.com/v1.0/me/calendarview', [
        'startDateTime' => $startDateTime,
        'endDateTime' => $endDateTime,
        '$select' => 'subject,start,end,location,calendar,onlineMeeting,isOnlineMeeting,onlineMeetingUrl,body,attendees',
        '$orderby' => 'start/dateTime desc',
        '$top' => 100,
    ]);

    return $eventsResponse->json();
}


// public function getEvents()
// {
//     $accessToken = session('microsoft_access_token');

//     if (!$accessToken) {
//         return response()->json(['error' => 'Not authenticated with Microsoft'], 401);
//     }

//     // Get current date in UTC
//     $startDateTime = date('Y-m-d\TH:i:s\Z', strtotime('-6 months'));
//     $endDateTime = date('Y-m-d\TH:i:s\Z', strtotime('+6 months'));

//     // Fetch the newest events by ordering in descending order
//     $eventsResponse = Http::withHeaders([
//         'Authorization' => 'Bearer ' . $accessToken,
//         'Accept' => 'application/json',
//         'Prefer' => 'outlook.timezone="UTC"'
//     ])->get('https://graph.microsoft.com/v1.0/me/calendarview', [
//         'startDateTime' => $startDateTime,
//         'endDateTime' => $endDateTime,
//         '$select' => 'subject,start,end,location,calendar,onlineMeeting,isOnlineMeeting,onlineMeetingUrl,body,attendees',
//         '$orderby' => 'start/dateTime desc', // Order by newest first
//         '$top' => 100,
//     ]);

//     // Log the full response for debugging
//     \Log::info('Events Response:', $eventsResponse->json());

//     return $eventsResponse->json();
// }

    

public function deleteEvent($eventId)
    {
        $accessToken = session('microsoft_access_token');

        if (!$accessToken) {
            return response()->json(['error' => 'Not authenticated with Microsoft'], 401);
        }

        try {
            // Send DELETE request to Microsoft Graph API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->delete("https://graph.microsoft.com/v1.0/me/events/{$eventId}");

            if ($response->status() === 204) {
                return response()->json(['message' => 'Event deleted successfully']);
            }

            // If we reach here, there was an error
            \Log::error('Failed to delete Microsoft event:', $response->json());
            return response()->json([
                'error' => 'Failed to delete event',
                'details' => $response->json() 
            ], $response->status());
        } catch (\Exception $e) {
            \Log::error('Exception during event deletion: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete event', 'message' => $e->getMessage()], 500);
        }
    }

    // public function createTeamsMeeting(Request $request)
    // {
    //     $activityId = $request->input('activityId');
    //     // Retrieve the activity by ID
    //     $activity = Activity::find($activityId);
    
    //     if (!$activity) {
    //         \Log::error('Activity not found with ID: ' . $activityId);
    //         return null;
    //     }
    
    //     // Get the Microsoft token from the user's session
    //     $microsoftAccessToken = session('microsoft_access_token');
    //     if (!$microsoftAccessToken) {
    //         \Log::error('Microsoft access token not found.');
    //         return response()->json(['error' => 'Microsoft access token not found.'], 400);
    //     }

    //     if (!$microsoftAccessToken) {
    //         // User not authenticated with Microsoft, handle accordingly
    //         \Log::error('Microsoft access token not found.');
    //         return null;
    //     }
        
    //     // Create meeting request body
    //     $meetingData = [
    //         'subject' => $activity->aktivita,
    //         'start' => [
    //             'dateTime' => date('Y-m-d\TH:i:s', strtotime($activity->datumCas)),
    //             'timeZone' => 'UTC'
    //         ],
    //         'end' => [
    //             'dateTime' => date('Y-m-d\TH:i:s', strtotime($activity->koniec)),
    //             'timeZone' => 'UTC'
    //         ],
    //         'isOnlineMeeting' => true,
    //         'onlineMeetingProvider' => 'teamsForBusiness'
    //     ];
        
    //     // Make API request to Microsoft Graph
    //     try {
    //         $client = new \GuzzleHttp\Client();
    //         $response = $client->post('https://graph.microsoft.com/v1.0/me/events', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . $microsoftAccessToken,
    //                 'Content-Type' => 'application/json'
    //             ],
    //             'json' => $meetingData
    //         ]);
            
    //         $responseData = json_decode($response->getBody(), true);
    
    //         // Extract the Teams meeting link
    //         $joinUrl = $responseData['onlineMeeting']['joinUrl'] ?? null;
            
    //         if ($joinUrl) {
    //             $activity->miesto_stretnutia = $joinUrl;
    //             $activity->save();
    //             return $joinUrl; // Return the meeting link
    //         } else {
    //             \Log::error('Teams meeting link not found.');
    //             return null;
    //         }
            
    //     } catch (\Exception $e) {
    //         \Log::error('Failed to create Teams meeting: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    public function createTeamsMeeting(Request $request)
{
    $activityId = $request->input('activityId');
    
    // Retrieve the activity by ID with contact relationship
    $activity = Activity::with('contact')->find($activityId);

    if (!$activity) {
        \Log::error('Activity not found with ID: ' . $activityId);
        return response()->json(['error' => 'Activity not found'], 404);
    }

    // Get the Microsoft token from the user's session
    $microsoftAccessToken = session('microsoft_access_token');
    if (!$microsoftAccessToken) {
        \Log::error('Microsoft access token not found.');
        return response()->json(['error' => 'Microsoft access token not found'], 400);
    }
    
    // Create meeting request body
    $meetingData = [
        'startDateTime' => date('Y-m-d\TH:i:s', strtotime($activity->datumCas)),
        'endDateTime' => date('Y-m-d\TH:i:s', strtotime($activity->koniec)),
        'subject' => $activity->aktivita,
        'participants' => [
            'organizer' => [
                'identity' => [
                    'user' => [
                        'id' => 'your_user_id'
                    ]
                ]
            ],
            'attendees' => [
                [
                    'identity' => [
                        'user' => [
                            'email' => $activity->contact->email
                        ]
                    ]
                ]
            ]
        ]
    ];
    
    // Add contact as attendee if email exists
    if (!empty($activity->contact) && !empty($activity->contact->email)) {
        $meetingData['attendees'] = [
            [
                'emailAddress' => [
                    'address' => $activity->contact->email,
                    'name' => $activity->contact->meno . ' ' . $activity->contact->priezvisko
                ],
                'type' => 'required'
            ]
        ];
    }
    
    try {
        // Use Http facade instead of Guzzle client for consistency
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $microsoftAccessToken,
            'Content-Type' => 'application/json'
        ])->post('https://graph.microsoft.com/v1.0/me/onlineMeetings', $meetingData);
        
        
        if ($response->failed()) {
            \Log::error('Failed to create Microsoft event:', $response->json());
            return response()->json([
                'error' => 'Failed to create event',
                'details' => $response->json()
            ], $response->status());
        }
        
        $responseData = $response->json();

        // Extract the Teams meeting link
        $joinUrl = $responseData['onlineMeeting']["joinUrl"] ?? ($responseData["meetingLink"] ?? null);

        
        if ($joinUrl) {
            // Update the activity with the meeting link and ID
            $activity->miesto_stretnutia = $joinUrl;
            $activity->teams_meeting_id = $responseData['id'] ?? null;
            $activity->save();
            
            return response()->json([
                'success' => true,
                'joinUrl' => $joinUrl,
                'eventId' => $responseData['id'] ?? null,
                'message' => 'Teams meeting created successfully'
            ]);
        } else {
            \Log::error('Teams meeting link not found in response.');
            return response()->json(['error' => 'Teams meeting link not found in response'], 500);
        }
        
    } catch (\Exception $e) {
        \Log::error('Failed to create Teams meeting: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create Teams meeting: ' . $e->getMessage()], 500);
    }
}

    public function logout()
    {
        session()->forget('microsoft_access_token');
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function createEvent(Request $request)
{
    $accessToken = session('microsoft_access_token');

    if (!$accessToken) {
        return response()->json(['error' => 'Not authenticated with Microsoft'], 401);
    }

    // Validate the incoming request
    $validated = $request->validate([
        'subject' => 'required|string',
        'start' => 'required|string',
        'end' => 'required|string',
        'location' => 'nullable|string',
        'body' => 'nullable|string',
        'attendees' => 'nullable|array',
        'isOnlineMeeting' => 'nullable|boolean'
    ]);

    // Format the event data for Microsoft Graph API
    $eventData = [
        'subject' => $validated['subject'],
        'start' => [
            'dateTime' => Carbon::parse($validated['start'])->format('Y-m-d\TH:i:s'),
            'timeZone' => 'UTC'
        ],
        'end' => [
            'dateTime' => Carbon::parse($validated['end'])->format('Y-m-d\TH:i:s'),
            'timeZone' => 'UTC'
        ],
        'isOnlineMeeting' => $validated['isOnlineMeeting'] ?? false,
    ];

    // Add location if provided
    if (isset($validated['location'])) {
        $eventData['location'] = [
            'displayName' => $validated['location']
        ];
    }

    // Add body content if provided
    if (isset($validated['body'])) {
        $eventData['body'] = [
            'contentType' => 'HTML',
            'content' => $validated['body']
        ];
    }

    // Add attendees if provided
    if (isset($validated['attendees']) && is_array($validated['attendees'])) {
        $eventData['attendees'] = array_map(function($attendee) {
            return [
                'emailAddress' => [
                    'address' => $attendee['email'],
                    'name' => $attendee['name'] ?? ''
                ],
                'type' => $attendee['type'] ?? 'required'
            ];
        }, $validated['attendees']);
    }

    // Send request to Microsoft Graph API
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type' => 'application/json'
    ])->post('https://graph.microsoft.com/v1.0/me/events', $eventData);

    if ($response->failed()) {
        \Log::error('Failed to create Microsoft event:', $response->json());
        return response()->json([
            'error' => 'Failed to create event',
            'details' => $response->json()
        ], 400);
    }

    return response()->json($response->json());
}

//     public function createTeamsMeeting(Request $request)
// {
//     // Validate the incoming request
//     $validated = $request->validate([
//         'activity_id' => 'required|integer|exists:activities,id',
//     ]);

//     // Retrieve the activity
//     $activity = Activity::with('contact')->findOrFail($validated['activity_id']);
    
//     // Get access token (ensure you have Microsoft auth set up)
//     $accessToken = session('microsoft_access_token');
//     if (!$accessToken) {
//         return response()->json(['error' => 'Not authenticated with Microsoft'], 401);
//     }
    
//     // Format the meeting data
//     $eventData = [
//         'subject' => $activity->aktivita . ' - ' . ($activity->contact->meno ?? 'Contact'),
//         'start' => [
//             'dateTime' => Carbon::parse($activity->datumCas)->format('Y-m-d\TH:i:s'),
//             'timeZone' => 'UTC'
//         ],
//         'end' => [
//             'dateTime' => Carbon::parse($activity->koniec)->format('Y-m-d\TH:i:s'),
//             'timeZone' => 'UTC'
//         ],
//         'isOnlineMeeting' => true,
//         'onlineMeetingProvider' => 'teamsForBusiness',
//     ];
    
//     // Add location if provided
//     if ($activity->miesto_stretnutia) {
//         $eventData['location'] = [
//             'displayName' => $activity->miesto_stretnutia
//         ];
//     }
    
//     // Add body content (notes)
//     $eventData['body'] = [
//         'contentType' => 'HTML',
//         'content' => $activity->poznamka ?? 'Meeting notes'
//     ];
    
//     // Add attendee (contact's email)
//     if ($activity->contact && $activity->contact->email) {
//         $eventData['attendees'] = [
//             [
//                 'emailAddress' => [
//                     'address' => $activity->contact->email,
//                     'name' => $activity->contact->meno ?? 'Contact'
//                 ],
//                 'type' => 'required'
//             ]
//         ];
//     }
    
//     // Send request to Microsoft Graph API
//     $response = Http::withHeaders([
//         'Authorization' => 'Bearer ' . $accessToken,
//         'Content-Type' => 'application/json'
//     ])->post('https://graph.microsoft.com/v1.0/me/events', $eventData);
    
//     if ($response->failed()) {
//         \Log::error('Failed to create Microsoft event:', $response->json());
//         return response()->json([
//             'error' => 'Failed to create event',
//             'details' => $response->json()
//         ], 400);
//     }
    
//     // Store the meeting info with the activity
//     $meetingInfo = $response->json();
//     $activity->teams_meeting_id = $meetingInfo['id'] ?? null;
//     $activity->teams_meeting_url = $meetingInfo['onlineMeeting']['joinUrl'] ?? null;
//     $activity->save();
    
//     return response()->json([
//         'success' => true,
//         'meeting' => $meetingInfo,
//         'activity' => $activity
//     ]);
// }

}