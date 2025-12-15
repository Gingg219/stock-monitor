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
        if ($this->isValidKey($filters, 'search')) {
            $search = $filters['search'];

            $model = $model->where(function ($q) use ($search) {
                $q->where('unit_code', 'like', "%{$search}%")
                ->orWhereHas('part', function ($p) use ($search) {
                    $p->where('part_no', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            });
        }
        
        // filter unit_code riêng
        if ($this->isValidKey($filters, 'unit_code')) {
            $model = $model->where('unit_code', 'like', "%{$filters['unit_code']}%");
        }

        // filter part
        if ($this->isValidKey($filters, 'part_id')) {
            $model = $model->where('part_id', $filters['part_id']);
        }

        // status = 1 giá trị
        if ($this->isValidKey($filters, 'status')) {
            $model = $model->where('status', $filters['status']);
        }

        // status IN (...)
        if ($this->isValidKey($filters, 'status_in')) {
            $model = $model->whereIn('status', (array) $filters['status_in']);
        }

        // warehouse
        if ($this->isValidKey($filters, 'warehouse_id')) {
            $model = $model->where('warehouse_id', $filters['warehouse_id']);
        }
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
