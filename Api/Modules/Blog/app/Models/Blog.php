<?php

namespace Modules\Blog\app\Models;

use App\Models\Translation;
use App\Models\User;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;


class Blog extends Model
{
    use DeleteTranslations;
    protected $fillable = [
        'admin_id',
        'category_id',
        'title',
        'slug',
        'description',
        'image',
        'views',
        'visibility',
        'status',
        'schedule_date',
        'tag_name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_image',
    ];
    public $translationKeys = [
        'title',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function relatedBlogs()
    {
        $query = Blog::where('id', '!=', $this->id)
            ->where('status', 1)
            ->where(function ($q) {
                $q->where('category_id', $this->category_id);
                foreach (explode(',', $this->tag_name) as $tag) {
                    $q->orWhere('tag_name', 'LIKE', "%$tag%");
                }
            })
            ->limit(5);

        return $query;
    }

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
}
