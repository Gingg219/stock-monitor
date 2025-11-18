<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\PartsRepository;
use App\Models\Parts;
use App\Validators\PartsValidator;

/**
 * Class PartsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class PartsRepositoryEloquent extends BaseRepository implements PartsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Parts::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
