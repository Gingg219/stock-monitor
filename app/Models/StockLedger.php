<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class StockLedger.
 *
 * @package namespace App\Models;
 */
class StockLedger extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'occurred_at',
        'warehouse_id',
        'slot_id',
        'part_id',
        'qty_change',
        'movement_line_id'
    ];

}
