<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // Define the table if it's not pluralized
    protected $table = 'posts';

    // Specify which attributes are mass-assignable
    protected $fillable = [
        'user_id',
        'content',
        'like_count',
    ];

    /**
     * Define the relationship with the User model.
     * Each post belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the Reply model.
     * Each post can have multiple replies.
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    // Post.php
public function images()
{
    return $this->hasMany(PostImage::class);
}

}
?>
