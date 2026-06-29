<?php

namespace Modules\Branch\app\Models;

use App\Models\Translation;
use App\Models\UniversalNotification;
use App\Models\User;
use App\Traits\DeleteTranslations;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\Chat\app\Models\Chat;
use Modules\Feedback\app\Models\Review;
use Modules\Location\app\Models\Area;
use Modules\Location\app\Models\City;
use Modules\Location\app\Models\State;
use Modules\Product\app\Models\Product;
use Modules\SystemCore\app\Models\Media;

class Branch extends Model
{
    use SoftDeletes, DeleteTranslations, HasMedia;

    protected $dates = ['deleted_at'];
    protected $table = 'branches';
    protected $guarded = [];
    protected $fillable = [
        'zone_id',
        'state_id',
        'city_id',
        'area_id',
        'is_web',
        'is_main',
        'type',
        'name',
        'slug',
        'phone',
        'email',
        'logo',
        'banner',
        'address',
        'latitude',
        'longitude',
        'is_featured',
        'opening_time',
        'closing_time',
        'tax',
        'tax_number',
        'delivery_charge',
        'delivery_time',
        'delivery_self_system',
        'delivery_take_away',
        'off_day',
        'gallery_images',
        'meta_title',
        'meta_description',
        'meta_image',
        'status',
        'online_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'online_at' => 'datetime',
        'gallery_images' => 'array',
        'is_main' => 'boolean',
        'is_web' => 'boolean',
    ];

    public $translationKeys = [
        'name',
        'slug',
        'address',
    ];


    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }

    public function getRatingAttribute(): float
    {
        $average = Review::where('reviewable_type', Product::class)
            ->where('status', 'active')
            ->avg('rating');

        if (is_null($average)) {
            return 0; // No reviews
        }

        // Clamp between 1 and 5 only if there's an actual rating
        return max(1, min(5, round($average, 2)));
    }


    public function chats()
    {
        return $this->morphMany(Chat::class, 'user');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'fileable', 'user_type', 'user_id');
    }


    public function notifications()
    {
        return $this->hasMany(UniversalNotification::class, 'notifiable_id')->where('notifiable_type', 'admin');
    }


}
