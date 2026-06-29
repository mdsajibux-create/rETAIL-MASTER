<?php

namespace Modules\Blog\app\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class BlogView extends Model
{
    protected $fillable = ['blog_id', 'user_id', 'ip_address'];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function user()
    {
        return $this->belongsTo(Customer::class);
    }
}
