<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\MovementsRepository;
use App\Models\Movements;
use App\Validators\MovementsValidator;

/**
 * Class MovementsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class MovementsRepositoryEloquent extends BaseRepository implements MovementsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Movements::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
