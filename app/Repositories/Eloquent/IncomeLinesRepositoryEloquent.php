<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\IncomeLinesRepository;
use App\Models\IncomeLines;
use App\Repositories\Traits\RepositoryTraits;
use App\Validators\IncomeLinesValidator;

/**
 * Class IncomeLinesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class IncomeLinesRepositoryEloquent extends BaseRepository implements IncomeLinesRepository
{
    use RepositoryTraits;
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
    
}
