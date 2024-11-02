<?php


use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\contactController;
use App\Http\Controllers\ActivityController;


Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('add-share-id/{id}', 'addShareID');
    Route::post('null-share-id/{id}', 'setShareIDfromArray');
    Route::get('get-user', 'gerUser');
    Route::get('test', function () {
        return response()->json(['message' => 'Hello World!']);
    });
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
    Route::get(' /{creatorId}', 'getActivitiesByCreator');
});

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
