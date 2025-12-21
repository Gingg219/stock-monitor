<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\TiersRepository;
use App\Models\Tiers;
use App\Repositories\Traits\RepositoryTraits;
use App\Validators\TiersValidator;

/**
 * Class TiersRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class TiersRepositoryEloquent extends BaseRepository implements TiersRepository
{
    use RepositoryTraits;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Tiers::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */

    public function buildQuery($model, $filters)
    {
        if ($this->isValidKey($filters, 'rack_id')) {
            $model->where('rack_id', $filters['rack_id']);
        }

        if ($this->isValidKey($filters, 'search')) {
            $model->where('level_no', 'like', "%{$filters['search']}%");
        }

        return $model;
    }
    
}
