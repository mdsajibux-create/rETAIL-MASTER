<?php

namespace Modules\Coupon\app\Models;

use App\Models\Translation;
use App\Models\User;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory,DeleteTranslations;

    protected $fillable = [
        'title',
        'description',
        'image',
        'status',
        'created_by'
    ];

    public $translationKeys = [
        'title',
        'description'
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function couponLines()
    {
        return $this->hasMany(CouponLine::class);
    }

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
}
