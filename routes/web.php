<?php


use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\contactController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CallListController;
use App\Http\Controllers\CalendarSharingRequestController;
use App\Http\Controllers\statisticsController;
use App\Http\Controllers\MicrosoftCalendarController;


Route::get('/auth/callbackAzure', [MicrosoftCalendarController::class, 'callbackAzure']);
Route::get('/get-events', [MicrosoftCalendarController::class, 'getEvents']);
Route::post('/logout', [MicrosoftCalendarController::class, 'logout']);
Route::post('/create-microsoft-event', [MicrosoftCalendarController::class, 'createEvent']);
Route::delete('events/{eventId}', [MicrosoftCalendarController::class, 'deleteEvent']);
Route::post('/create-teams-meeting', [MicrosoftCalendarController::class, 'createTeamsMeeting']);
Route::get('/check-auth', [MicrosoftCalendarController::class, 'checkAuth']);

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    //Route::post('add-share-id/{id}', 'addShareID');
    //Route::post('null-share-id/{id}', 'setShareIDfromArray');
    Route::get('get-user', 'gerUser');
    Route::get('get-hello', 'getHelloWorld');
});

Route::controller(contactController::class)->group(function () {
    Route::get('contacts', 'getContacts');
    Route::get('all-contacts', 'getAllContacts');
    Route::get('contact/{id}', 'getSingleContact');
    Route::post('post-create-contact', 'addContact');
    Route::put('post-update-contact/{id}', 'updateContact');
    Route::delete('delete-delete-contact/{id}', 'deleteContact');
    Route::get('get-users', 'getUsers');   
    Route::get('search-contacts', 'searchContacts'); 
    Route::patch('contact/{id}/email', 'updateEmail');
    Route::post('call-list', 'getContactsByIds');
    Route::post('post-create-contacts', 'addContacts');
    Route::get('get', 'getHelloWorld');
});

Route::controller(ActivityController::class)->group(function () {
    Route::get('activities', 'getActivities');
    Route::get('contacts/{contactId}/activities', 'getActivitiesByContact');
    Route::post('add-activity', 'addActivity');
    Route::put('update-activities/{id}', 'updateActivity');
    Route::delete('delete-activities/{id}', 'deleteActivity');
    Route::get('get-activities-diary', 'getActivitiesDiary');
    Route::post('get-activities', 'getActivitiesByUserIds'); //noa funcia na branie sharovanych userov
    Route::get('activities/{id}', 'getActivityById');
    Route::get('get-activities-by-creator/{creatorId}', 'getActivitiesByCreator');
    Route::post('add-share-id/{id}', 'addShareID');
    Route::post('null-share-id/{id}', 'setShareIDfromArray');
});

Route::controller(CallListController::class)->group(function () {
      // Fetch all call lists (index)
      Route::get('call-lists', 'index');
    
      // Create a new call list (store)
      Route::post('call-lists', 'store');
      
      // Fetch a specific call list (show)
      Route::get('call-lists/{id}', 'show');
      
      // Update a specific call list (update)
      Route::put('call-lists/{id}', 'update');
      
      // Delete a specific call list (destroy)
      Route::delete('call-lists/{id}', 'destroy');
  
});

Route::controller(CalendarSharingRequestController::class)->group(function () {
    Route::get('get-their-requests', 'viewTheirCalendarRequests');
    Route::get('get-mine-requests', 'letThemViewMineCalendarRequests');
    Route::get('get-mine-requests-approval', 'letThemViewMineCalendarRequestsForApproval');
    Route::delete('delete-sharing-requests/{id}', 'destroy');
    Route::post('add-sharing-id/{userId}/{requestId}', 'addShareIDById');
    Route::post('post-sharing-request', 'store');
    Route::get('get-requests-history', 'returnHistory');
    
    Route::get('get-mine-requests-skuska', 'letThemViewMineCalendarRequestsSkuska');
    
    Route::get('get-mine-requests-tabulka', 'letThemViewMineCalendarRequestsTabulka');
    
    Route::post('add-sharing-id-table/{id}/{requestId}', 'addShareIDByIdTable');
    
    Route::get('who-see-my-cal', 'whoSeesMyCalendar');
});

Route::controller(statisticsController::class)->group(function () {
    Route::post('activity-statistics', 'getStatistics');
});
// Route::controller(CalendarSharingRequestController::class)->group(function () {
//      // Fetch all calendar sharing requests (index)
//      Route::get('calendar-sharing-requests', 'index');
    
//      // Fetch a specific calendar sharing request (show)
//      Route::get('calendar-sharing-requests/{id}', 'show');
     
//      // Create a new calendar sharing request (store)
//      Route::post('calendar-sharing-requests', 'store');
     
//      // Update a specific calendar sharing request (update)
//      Route::put('calendar-sharing-requests/{id}', 'update');
     
//      // Delete a specific calendar sharing request (destroy)
//      Route::delete('delete-sharing-request/{id}', 'destroy');
     
//      // show viewTheirCalendarRequests
//      Route::get('get-their-requests', 'viewTheirCalendarRequests');

//      // show letThemViewMineCalendarRequests
//      Route::get('get-mine-requests', 'letThemViewMineCalendarRequests');
// });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();

//     return redirect('/home');
// })->middleware(['auth', 'signed'])->name('verification.verify');

// Verify email
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');


Route::get('/email/verify', function () {
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/success', function () {
    return view('email.success', ['name' => 'aaa']);
})->name('verification.success');
