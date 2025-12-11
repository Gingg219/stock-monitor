<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\StorageUnitsRepository;
use App\Models\StorageUnits;
use App\Repositories\Traits\RepositoryTraits;
use App\Validators\StorageUnitsValidator;

/**
 * Class StorageUnitsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class StorageUnitsRepositoryEloquent extends BaseRepository implements StorageUnitsRepository
{
    
    use RepositoryTraits;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StorageUnits::class;
    }

    public function buildQuery($model, $filters)
    {
        // if($this->isValidKey($filters, 'income_lines')) {
        //     $incomeLineID = $filters['income_lines'];
        //     $model = $model->whereHas('income_lines', function ($query) use ($incomeLineID) {
        //         $query->where('income_lines.id', $incomeLineID);
        //     });
        // }
        
        return $model;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
