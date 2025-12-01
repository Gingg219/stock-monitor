<?php

namespace App\Repositories\Eloquent;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\StockLedgerRepository;
use App\Models\StockLedger;
use App\Validators\StockLedgerValidator;

/**
 * Class StockLedgerRepositoryEloquent.
 *
 * @package namespace App\Repositories\Eloquent;
 */
class StockLedgerRepositoryEloquent extends BaseRepository implements StockLedgerRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StockLedger::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
