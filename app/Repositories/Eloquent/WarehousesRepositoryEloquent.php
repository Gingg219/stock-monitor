<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\WarehousesRepository;
use App\Models\Warehouses;
use App\Repositories\Traits\RepositoryTraits;

/**
 * Class WarehousesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class WarehousesRepositoryEloquent extends BaseRepository implements WarehousesRepository
{
    use RepositoryTraits;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Warehouses::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    function buildQuery($model, $filters)
    {
        // TODO: Implement buildQuery() method.
        if ($this->isValidKey($filters, 'status')) {
            $model = $model->where('status', $filters['status']);
        }

        if ($this->isValidKey($filters, 'search')) {
            $model = $model->where(function ($query) use ($filters) {
                $query->orWhere('category_cd', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $model;
    }
}
