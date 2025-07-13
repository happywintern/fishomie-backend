<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel's naming conventions)
    protected $table = 'subscriptions';

    // Define the columns that can be mass-assigned
    protected $fillable = [
        'user_id',
        'status',
        'amount',
        'payment_method',
        'transaction_id',
        'expires_at',
    ];

    // Define any relationships, if applicable (e.g., to User model)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Additional functionality, like custom accessors or mutators, can be added here
}
?>