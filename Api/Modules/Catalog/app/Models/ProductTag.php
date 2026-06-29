<?php

namespace Modules\Catalog\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\app\Models\Product;

class ProductTag extends Model
{
    protected $fillable = [
        'product_id',
        'tag_id'
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }

}
