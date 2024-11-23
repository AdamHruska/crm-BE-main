<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\User;

class contactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getContacts()
    {
        $user = auth()->user();
    
        // Retrieve contacts ordered by newest first and paginate
        $contacts = Contact::where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    
        return response()->json([
            'contacts' => $contacts,
            'message' => 'Contacts retrieved successfully',
            'status' => 200
        ]);
    }

    public function getAllContacts()
    {
        $user = auth()->user();
    
        // Retrieve contacts ordered by newest first
        $contacts = Contact::where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    
        return response()->json([
            'contacts' => $contacts,
            'message' => 'Contacts retrieved successfully',
            'status' => 200
        ]);
    }

    public function getUsers()
    {
        $user = auth()->user();
        $userId = $user->id;
        
        $users = User::where('id', '!=', $userId)->get();

        return response()->json([
            'users' => $users,
            'message' => 'Users retrieved successfully',
            'status' => 200
        ]);

        // Return all users (or you can filter it by some role or criteria)
        // $users = User::select('id', 'first_name', 'last_name')->get();

        // return response()->json([
        //     'users' => $users,
            
        // ]);
    }

    public function addContact(Request $request)
    {
        $user = auth()->user();

        // Validate the request
        $validatedData = $request->validate([
            'meno' => 'required|string|max:255',
            'priezvisko' => 'required|string|max:255',
            'cislo' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:contacts,email',
            'odporucitel' => 'required|string|max:255',
            'adresa' => 'nullable|string|max:255',
            'vek' => 'nullable|integer|min:0|max:150', // Ensure valid age input
            'zamestanie' => 'nullable|string|max:255',
            'poznamka' => 'nullable|string',
            'Investicny_dotaznik' => 'nullable|date'
        ]);

        // Calculate the year of birth from the age
        $currentYear = now()->year;
        $yearOfBirth = $currentYear - $validatedData['vek'];

        // Create a new contact
        $contact = new Contact();
        $contact->meno = $validatedData['meno'];
        $contact->priezvisko = $validatedData['priezvisko'];
        $contact->poradca = $user->id;
        $contact->cislo = $validatedData['cislo'];
        $contact->email = $validatedData['email'];
        $contact->odporucitel = $validatedData['odporucitel'];
        $contact->adresa = $validatedData['adresa'];
        $contact->rok_narodenia = $yearOfBirth; // Store calculated year of birth
        $contact->zamestanie = $validatedData['zamestanie'];
        $contact->poznamka = $validatedData['poznamka'];
        if (isset($validatedData['Investicny_dotaznik'])) {
            $contact->Investicny_dotaznik = $validatedData['Investicny_dotaznik'];
        }
        $contact->author_id = $user->id;
        $contact->save();

        // Return the response
        return response()->json([
            'contact' => $contact,
            'message' => 'Contact added successfully',
            'status' => 201
        ]);
    }

    // Delete a contact
    public function deleteContact($id)
    {
        $contact = Contact::find($id);
        if ($contact) {
            $contact->delete();
            return response()->json([
                'message' => 'Contact deleted successfully',
                'status' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'Contact not found',
                'status' => 404
            ]);
        }
    }

    // Get a single contact by ID
    public function getSingleContact($id)
    {
        $contact = Contact::find($id);
        if ($contact) {
            return response()->json([
                'contact' => $contact,
                'message' => 'Contact retrieved successfully',
                'status' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'Contact not found',
                'status' => 404
            ]);
        }
    }

    // Update a contact
    public function updateContact(Request $request, $id)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'meno' => 'nullable|string|max:255',
            'priezvisko' => 'nullable|string|max:255',
            'poradca' => 'nullable|string|max:255',
            'cislo' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'odporucitel' => 'nullable|string|max:255',
            'adresa' => 'nullable|string|max:255',
            'vek' => 'nullable|integer|min:0|max:150', // Validate age input
            'zamestanie' => 'nullable|string|max:255',
            'poznamka' => 'nullable|string',
            'Investicny_dotaznik' => 'nullable|date',
            'author_id' => 'nullable|integer'
        ]);
    
        // Find the contact to update
        $contact = Contact::find($id);
    
        // Check if contact exists
        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found',
                'status' => 404
            ], 404);
        }
    
        // Calculate the year of birth from the age
        $currentYear = now()->year;
        $yearOfBirth = $currentYear - $validated['vek'];
    
        // Update the contact
        $contact->meno = $validated['meno'];
        $contact->priezvisko = $validated['priezvisko'];
        $contact->poradca = $validated['poradca'];
        $contact->cislo = $validated['cislo'];
        $contact->email = $validated['email'];
        $contact->odporucitel = $validated['odporucitel'];
        $contact->adresa = $validated['adresa'];
        $contact->rok_narodenia = $yearOfBirth; // Update year of birth based on age
        $contact->zamestanie = $validated['zamestanie'];
        $contact->poznamka = $validated['poznamka'];
        $contact->Investicny_dotaznik = $validated['Investicny_dotaznik'];
        $contact->author_id = $validated['author_id'];
        $contact->save();
    
        // Return a response
        return response()->json([
            'contact' => $contact,
            'message' => 'Contact updated successfully',
            'status' => 200
        ]);
    }
    
    


    // Search for contacts
    // public function searchContacts(Request $request)
    // {
    //     $query = $request->input('query');

    //     // Assuming you want to search by name and email
    //     $contacts = Contact::where('meno', 'like', "%{$query}%")
    //                         ->orWhere('priezvisko', 'like', "%{$query}%")
    //                         ->get();

    //     return response()->json([
    //         'contacts' => $contacts,
    //         'message' => 'Contacts retrieved successfully',
    //         'status' => 200
    //     ]);
    // }

    public function searchContacts(Request $request)
    {
        $query = $request->input('query');
        $loggedInUserId = auth()->id(); // Get the ID of the logged-in user
        
        // Split the query by spaces into an array
        $names = explode(' ', $query);
        
        if (count($names) == 2) {
            // Search for 'first_name last_name', 'last_name first_name', or matches in 'odporucitel'
            $contacts = Contact::where('poradca', $loggedInUserId) // Ensure 'poradca' matches the logged-in user
                ->where(function ($q) use ($names) {
                    $q->where(function ($subQuery) use ($names) {
                            $subQuery->where('meno', 'like', "%{$names[0]}%")
                                     ->where('priezvisko', 'like', "%{$names[1]}%");
                        })
                      ->orWhere(function ($subQuery) use ($names) {
                            $subQuery->where('meno', 'like', "%{$names[1]}%")
                                     ->where('priezvisko', 'like', "%{$names[0]}%");
                        });
                })
                ->orWhere('odporucitel', 'like', "%{$query}%")
                ->get();
        } else {
            // If there aren't exactly two words, search for partial matches in 'meno', 'priezvisko', or 'odporucitel'
            $contacts = Contact::where('poradca', $loggedInUserId) // Ensure 'poradca' matches the logged-in user
                ->where(function ($q) use ($query) {
                    $q->where('meno', 'like', "%{$query}%")
                      ->orWhere('priezvisko', 'like', "%{$query}%")
                      ->orWhere('odporucitel', 'like', "%{$query}%");
                })
                ->get();
        }
        
        return response()->json([
            'contacts' => $contacts,
            'message' => 'Contacts retrieved successfully',
            'status' => 200
        ]);
    }
    
    public function updateEmail(Request $request, $id)
    {
        // Validate the email
        $validated = $request->validate([
            'email' => 'required|email|unique:contacts,email,' . $id, // Ensure the email is unique except for the current contact
        ]);

        // Find the contact
        $contact = Contact::find($id);

        // Check if contact exists
        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found',
                'status' => 404
            ], 404);
        }

        // Update the email
        $contact->email = $validated['email'];
        $contact->save();

        // Return the updated contact
        return response()->json([
            'contact' => $contact,
            'message' => 'Email updated successfully',
            'status' => 200
        ]);
    }

    public function getContactsByIds(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer'
            ]);
    
            // Get the authenticated user
            $user = auth()->user();
    
            // Retrieve contacts by the provided IDs
            // Only get contacts that belong to the authenticated user
            $contacts = Contact::whereIn('id', $validated['ids'])
                             ->where('author_id', $user->id)
                             ->get();
    
            return response()->json([
                'contacts' => $contacts,
                'message' => 'Contacts retrieved successfully',
                'status' => 200
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'status' => 422
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
