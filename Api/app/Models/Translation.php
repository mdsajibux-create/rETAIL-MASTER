<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $casts = [
        'translatable_id' => 'integer',
    ];
    
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'language',
        'key',
        'value',
    ];

    public function translatable()
    {
        return $this->morphTo();
    }
}
