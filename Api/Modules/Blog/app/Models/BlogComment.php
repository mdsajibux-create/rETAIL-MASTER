<?php

namespace Modules\Blog\app\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogComment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'blog_id',
        'user_id',
        'comment',
        'like_count',
        'dislike_count',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function scopeOrderByLikeDislikeRatio($query)
    {
        return $query->orderByRaw('like_count / (dislike_count + 1) DESC');
    }

    public function blogCommentReactions()
    {
        return $this->hasMany(BlogCommentReaction::class, 'blog_comment_id');
    }

}
