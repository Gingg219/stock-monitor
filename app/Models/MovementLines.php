<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class MovementLines.
 *
 * @package namespace App\Models;
 */
class MovementLines extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'movement_id',
        'part_id',
        'qty',
        'from_slot_id',
        'storage_unit_id',
        'lot_no',
        'expiry_date',
    ];

    public function stock_ledger()
    {
        return $this->hasMany(StockLedger::class, 'movement_line_id');
    }
}
