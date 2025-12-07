<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\IncomesRepository;
use App\Models\Incomes;
use App\Repositories\Traits\RepositoryTraits;
use App\Validators\IncomesValidator;

/**
 * Class IncomesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class IncomesRepositoryEloquent extends BaseRepository implements IncomesRepository
{
    use RepositoryTraits;
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
    public function buildQuery($model, $filters)
    {
        if($this->isValidKey($filters, 'income_lines')) {
            $incomeLineID = $filters['income_lines'];
            $model = $model->whereHas('income_lines', function ($query) use ($incomeLineID) {
                $query->where('income_lines.id', $incomeLineID);
            });
        }
        
        return $model;
    }

    // public function boot()
    // {
    //     $this->pushCriteria(app(RequestCriteria::class));
    // }
    
}
