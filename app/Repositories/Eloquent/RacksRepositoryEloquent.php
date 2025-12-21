<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\RacksRepository;
use App\Models\Racks;
use App\Repositories\Traits\RepositoryTraits;
use App\Validators\RacksValidator;

/**
 * Class RacksRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class RacksRepositoryEloquent extends BaseRepository implements RacksRepository
{
    use RepositoryTraits;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Racks::class;
    }

    
 function buildQuery($model, $filters)
    {
        if ($this->isValidKey($filters, 'warehouse_id')) {
            $model->where('warehouse_id', $filters['warehouse_id']);
        }

        if ($this->isValidKey($filters, 'search')) {
            $model->where('code', 'like', "%{$filters['search']}%");
        }

        return $model;
    }
    
}
