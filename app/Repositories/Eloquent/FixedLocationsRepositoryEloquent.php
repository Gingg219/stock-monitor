<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\FixedLocationsRepository;
use App\Models\FixedLocations;
use App\Validators\FixedLocationsValidator;

/**
 * Class FixedLocationsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class FixedLocationsRepositoryEloquent extends BaseRepository implements FixedLocationsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return FixedLocations::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
