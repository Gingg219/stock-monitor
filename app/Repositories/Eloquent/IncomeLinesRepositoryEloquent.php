<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\IncomeLinesRepository;
use App\Models\IncomeLines;
use App\Validators\IncomeLinesValidator;

/**
 * Class IncomeLinesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class IncomeLinesRepositoryEloquent extends BaseRepository implements IncomeLinesRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return IncomeLines::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
