<?php

namespace Modules\SystemCore\app\Models;

use Illuminate\Database\Eloquent\Model;

class Sitemap extends Model
{
    protected $fillable = [
        'filename',
        'generated_at',
        'size'
    ];
}
