<?php

namespace App\Repositories;

use Modules\Catalog\app\Models\ProductBrand;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 *
 * @package namespace App\Repositories;
 */
class ProductRepository extends BaseRepository
{

    public function model()
    {
        return ProductBrand::class;
    }

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }

}
