<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\IncomesRepository;
use App\Models\Incomes;
use App\Validators\IncomesValidator;

/**
 * Class IncomesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class IncomesRepositoryEloquent extends BaseRepository implements IncomesRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Incomes::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
