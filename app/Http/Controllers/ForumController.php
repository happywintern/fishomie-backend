<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Reply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class ForumController extends Controller
{
    public function getAllPosts()
{
    try {
        $posts = Post::with('user', 'replies.user', 'images')->get(); // Eager load users and replies
        return response()->json(['status' => 'success', 'posts' => $posts], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

    // Method to create a new post
    public function createPost(Request $request)
    {
        // Validate input
        $request->validate([
            'content' => 'required|string|max:5000',
            'images' => 'nullable|array|max:3', // Limit to 3 images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate each image
        ]);
    
        // Authenticate user via Bearer token
        $user = Auth::user();
    
        // Create the post
        $post = Post::create([
            'user_id' => $user->id,
            'content' => $request->input('content'),
        ]);
    
        // Save uploaded images, if any
        // Save uploaded images, if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store the image in public storage
                $imagePath = $image->store('post_images', 'public');

                // Generate the full URL for the stored image
                $imageUrl = url('storage/' . $imagePath);

                // Save the image URL in the post_images table
                $post->images()->create([
                    'image_url' => $imageUrl, // Use full URL instead of relative path
                ]);
            }
        }
    
        try { 
            return response()->json([
            'status' => 'success',
            'message' => 'Post created successfully',
            'post' => $post->load('images'), // Include associated images in the response
        ], 201);
    } catch (\Exception $e) {
        \Log::error('Error in createPost', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['status' => 'error', 'message' => 'Failed to create post'], 500);
    }
    
    }
    
    // Method to create a reply to a post
    public function createReply(Request $request, $postId)
    {
        // Validate input
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        // Check if the post exists
        $post = Post::findOrFail($postId);

        // Create a new reply
        $reply = Reply::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reply created successfully',
            'reply' => $reply,
        ], 201);
    }

 
    public function showPostWithReplies($id)
{
    try {
        \Log::info("showPostWithReplies called with ID: " . $id);

        // Fetch the post with the given ID, along with its replies, user information, and images
        $post = Post::with(['user', 'replies.user', 'images']) // Include 'images' relationship
            ->where('id', $id)
            ->first();

        if (!$post) {
            \Log::warning("Post not found with ID: " . $id);
            return response()->json(['status' => 'error', 'message' => 'Post not found'], 404);
        }

        \Log::info("Post retrieved successfully: " . json_encode($post));

        return response()->json(['status' => 'success', 'post' => $post], 200);
    } catch (\Exception $e) {
        // Log any exception to the Laravel log
        \Log::error("Error in showPostWithReplies: " . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
    }
}


    public function toggleLike($postId, Request $request) {

        if (!$request->user()) {
            \Log::error('User is not authenticated.');
            return response()->json(['error' => 'User is not authenticated'], 401);
        }
        
    $userId = $request->user()->id;

    // Check if the like exists
    $like = DB::table('likes')
                ->where('post_id', $postId)
                ->where('user_id', $userId)
                ->first();

    if ($like) {
        

        // If like exists, remove it
        DB::table('likes')
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->delete();

        // Update like_count in posts table
        DB::table('posts')->where('id', $postId)->decrement('like_count');

        return response()->json(['message' => 'Like removed'], 200);
    } else {
        // If like does not exist, add it
        DB::table('likes')->insert([
            'post_id' => $postId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update like_count in posts table
        DB::table('posts')->where('id', $postId)->increment('like_count');

        return response()->json(['message' => 'Like added'], 201);
    }
    
}
public function getUserPostHistory($userId)
{
    try {
        $posts = Post::with(['user', 'images']) // Include 'images' relationship
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json(['status' => 'success', 'posts' => $posts], 200);
    } catch (\Exception $e) {
        \Log::error("Error in getUserPostHistory: " . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Could not fetch user posts'], 500);
    }
}

 
    
}




