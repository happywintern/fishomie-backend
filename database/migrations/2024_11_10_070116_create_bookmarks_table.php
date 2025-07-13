<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()

    {
  
      Schema::create('bookmarks', function (Blueprint $table) {
  
        $table->id(); // Primary key
  
        $table->unsignedBigInteger('user_id'); // Foreign key to users
  
        $table->unsignedBigInteger('post_id'); // Foreign key to posts
  
        $table->timestamps();
  
  
  
        // Foreign key constraints
  
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
  
        $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
  
  
  
        // Unique constraint to prevent duplicate bookmarks by the same user on the same post
  
        $table->unique(['user_id', 'post_id']);
  
      });
  
    }
  
  };
