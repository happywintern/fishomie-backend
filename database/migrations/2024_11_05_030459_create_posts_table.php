<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('posts', function (Blueprint $table) {
            $table->id();                             // Post ID (BIGINT UNSIGNED)
            $table->unsignedBigInteger('user_id');    // Foreign key to users table (BIGINT UNSIGNED)
            $table->text('content');                  // Content of the post
            $table->integer('like_count')->default(0); // Like count, default to 0
            $table->timestamps();
            $table->string('image_url')->nullable()->after('content'); // Add image_url column after content

            // Define foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('replies', function (Blueprint $table) {
            $table->id();                              // Reply ID (BIGINT UNSIGNED)
            $table->unsignedBigInteger('post_id');     // Foreign key to posts table (BIGINT UNSIGNED)
            $table->unsignedBigInteger('user_id');     // Foreign key to users table (BIGINT UNSIGNED)
            $table->text('content');                   // Content of the reply
            $table->timestamps();
            $table->string('image_url')->nullable()->after('content'); // Add image_url column after content
            // Define foreign key constraints
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('replies');
    
    }
};
