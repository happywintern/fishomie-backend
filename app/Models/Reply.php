<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    // Define the table if not pluralized
    protected $table = 'replies';

    // Specify which attributes are mass-assignable
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
    ];

    /**
     * Define the relationship with the Post model.
     * Each reply belongs to a single post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Define the relationship with the User model.
     * Each reply belongs to a single user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
