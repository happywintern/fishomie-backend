<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\PaymentController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::put('/user/subscribe', [AuthController::class, 'subscribe'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/profile/edit', [ProfileController::class, 'update']);
    Route::put('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::delete('/profile/delete', [ProfileController::class, 'deleteAccount']);

});

Route::get('user/{userId}/posts', [ForumController::class, 'getUserPostHistory']);


Route::post('/profile/upload-picture', [ProfileController::class, 'uploadPicture'])->middleware('auth:sanctum');

Route::get('/forum/posts', [ForumController::class, 'getAllPosts']);

Route::middleware('auth:sanctum')->group(function () {
    // Route for creating a post
    Route::post('/forum/posts', [ForumController::class, 'createPost']);

    // Route for creating a reply to a post
    Route::post('/forum/posts/{post}/replies', [ForumController::class, 'createReply']);

    //Get posts by its id
    Route::patch('forum/posts/{id}/like', [ForumController::class, 'toggleLike']);


});

Route::get('forum/posts/{id}', [ForumController::class, 'showPostWithReplies']);

//Bookmarks api
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookmarks', [BookmarkController::class, 'addBookmark']);
    Route::get('/bookmarks', [BookmarkController::class, 'getBookmarks']);
    Route::delete('/bookmarks/{post_id}', [BookmarkController::class, 'deleteBookmark']);
  });
  
//subscription
Route::post('/create-transaction', [PaymentController::class, 'createTransaction'])->middleware('auth:sanctum');
Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']);

Route::post('/test-webhook', function () {
    return response()->json(['message' => 'Webhook received']);
});

Route::post('charge', [PaymentController::class, 'chargeTransaction'])->middleware('auth:sanctum');



