<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\SlotsRepository;
use App\Models\Slots;
use App\Repositories\Traits\RepositoryTraits;
use App\Validators\SlotsValidator;

/**
 * Class SlotsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class SlotsRepositoryEloquent extends BaseRepository implements SlotsRepository
{
    use RepositoryTraits;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Slots::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function buildQuery($model, $filters)
    {
        if ($this->isValidKey($filters, 'tier_id')) {
            $model->where('tier_id', $filters['tier_id']);
        }

        if ($this->isValidKey($filters, 'search')) {
            $model->where('code', 'like', "%{$filters['search']}%");
        }

        return $model;
    }
    
}
