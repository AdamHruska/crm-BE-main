<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\contactController;
use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// // Resend link to verify email
// Route::post('/email/verify/resend', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();
//     return back()->with('message', 'Verification link sent!');
// })->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('add-share-id/{id}', 'addShareID');
    Route::post('null-share-id', 'setShareIDtoNull');
    Route::get('test', function() {
        return "test";
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
    Route::get('activities/{id}', 'getActivityById');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
