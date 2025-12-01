<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\VendorsRepository;
use App\Models\Vendors;
use App\Validators\VendorsValidator;

/**
 * Class VendorsRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class VendorsRepositoryEloquent extends BaseRepository implements VendorsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Vendors::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
