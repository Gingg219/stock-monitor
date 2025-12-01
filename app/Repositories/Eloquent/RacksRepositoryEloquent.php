<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\RacksRepository;
use App\Models\Racks;
use App\Validators\RacksValidator;

/**
 * Class RacksRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class RacksRepositoryEloquent extends BaseRepository implements RacksRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Racks::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
