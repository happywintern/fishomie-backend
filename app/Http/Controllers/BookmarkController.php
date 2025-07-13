<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BookmarkController extends Controller

{

  // Add a bookmark

  public function addBookmark(Request $request)
  {
    $userId = Auth::id(); // Get the authenticated user ID
    $postId = $request->post_id;
    // Check if the bookmark already exists
    $existingBookmark = Bookmark::where('user_id', $userId)->where('post_id', $postId)->first();
    if ($existingBookmark) {

      return response()->json(['status' => 'error', 'message' => 'Bookmark already exists'], 400);
    }
    // Add the bookmark if it doesn't exist
    Bookmark::create([
      'user_id' => $userId,
      'post_id' => $postId,
    ]);
    return response()->json(['status' => 'success', 'message' => 'Bookmark added successfully'], 201);

  }
  // Retrieve all bookmarks for the authenticated user

  public function getBookmarks()
  {
      try {
          $userId = auth()->id(); // Retrieve the authenticated user's ID
          \Log::info("Fetching bookmarks for authenticated user ID: " . $userId);
  
          $bookmarks = Bookmark::where('user_id', $userId)
              ->with('post.user', 'post.images') // Include post, user, and post images
              ->get();
  
          return response()->json([
              'status' => 'success',
              'bookmarks' => $bookmarks
          ]);
      } catch (\Exception $e) {
          \Log::error("Error in showBookmarks: " . $e->getMessage());
          return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
      }
  }
    // Delete a specific bookmark
  public function deleteBookmark($postId)

  {

    $userId = Auth::id(); // Get the authenticated user ID
    $bookmark = Bookmark::where('user_id', $userId)->where('post_id', $postId)->first();
    if (!$bookmark) {
      return response()->json(['status' => 'error', 'message' => 'Bookmark not found'], 404);
    }
    $bookmark->delete();
    return response()->json(['status' => 'success', 'message' => 'Bookmark deleted successfully'], 200);
  }
}



?>