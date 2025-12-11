<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\SlotsRepository;
use App\Models\Slots;
use App\Repositories\Traits\RepositoryTraits;
use App\Validators\SlotsValidator;

/**
 * Class SlotsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class SlotsRepositoryEloquent extends BaseRepository implements SlotsRepository
{
    use RepositoryTraits;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Slots::class;
    }

    public function buildQuery($model, $filters)
    {
        return $model;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
