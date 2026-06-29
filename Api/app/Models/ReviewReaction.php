<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Feedback\app\Models\Review;

class ReviewReaction extends Model
{
    protected $fillable = [
        'review_id', 'user_id', 'reaction_type'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}
