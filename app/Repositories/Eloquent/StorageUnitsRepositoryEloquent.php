<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\StorageUnitsRepository;
use App\Models\StorageUnits;
use App\Validators\StorageUnitsValidator;

/**
 * Class StorageUnitsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class StorageUnitsRepositoryEloquent extends BaseRepository implements StorageUnitsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StorageUnits::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
