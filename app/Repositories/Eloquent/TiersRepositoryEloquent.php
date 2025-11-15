<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\TiersRepository;
use App\Models\Tiers;
use App\Validators\TiersValidator;

/**
 * Class TiersRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class TiersRepositoryEloquent extends BaseRepository implements TiersRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Tiers::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
