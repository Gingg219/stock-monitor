<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\WarehousesRepository;
use App\Models\Warehouses;
use App\Validators\WarehousesValidator;

/**
 * Class WarehousesRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class WarehousesRepositoryEloquent extends BaseRepository implements WarehousesRepository
{
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

    public function getWarehouseTree($filters = [])
    {
        $query = $this->model->with([
            'racks.tiers.slots.fixed_locations.parts'
        ]);

        if (!empty($filters['warehouse_id'])) {
            $query->where('id', $filters['warehouse_id']);
        }

        // dd($query->toSql());

        return $query->get();
    }
}
