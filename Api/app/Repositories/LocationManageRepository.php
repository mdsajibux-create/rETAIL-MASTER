<?php

namespace App\Repositories;

use App\Interfaces\LocationManageInterface;
use App\Models\Translation;
use Modules\Location\app\Models\State;

class LocationManageRepository implements LocationManageInterface
{
    public function __construct(
        protected State $state,
        protected Translation $translation
    ){

    }

    public function translationKeysForBlog(): mixed
    {
        return $this->state->translationKeys;
    }


}
