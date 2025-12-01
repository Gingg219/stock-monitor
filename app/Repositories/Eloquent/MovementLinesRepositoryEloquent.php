<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\MovementLinesRepository;
use App\Models\MovementLines;
use App\Validators\MovementLinesValidator;

/**
 * Class MovementLinesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class MovementLinesRepositoryEloquent extends BaseRepository implements MovementLinesRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return MovementLines::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
