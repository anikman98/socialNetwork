<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\FriendsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(AuthController::class)->group(function(){
        Route::get('/logout', 'logout');
    });
    
    Route::controller(FriendsController::class)->group(function(){
        Route::get('/search', 'search');
        Route::get('/friends', 'friends');
        Route::get('/add-friend/{id}', 'addFriend');
        Route::get('/fetch-requests', 'fetchRequests');
        Route::get('/accept/{id}', 'acceptRequest');
        Route::get('/reject/{id}', 'rejectRequest');
        Route::get('/user/{id}', 'userProfile');
    });


});

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});
