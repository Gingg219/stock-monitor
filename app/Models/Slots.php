<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Slots.
 *
 * @package namespace App\Models;
 */
class Slots extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tier_id',
        'code',
        'allowed_unit',
        'is_active',
    ];

    public function fixed_locations()
    {
        return $this->hasMany(FixedLocations::class, 'slot_id');
    }

    public function stock_ledger()
    {
        return $this->hasMany(StockLedger::class, 'slot_id');
    }

    public function movement_lines1()
    {
        return $this->hasMany(MovementLines::class, 'from_slot_id');
    }

    public function movement_lines2()
    {
        return $this->hasMany(MovementLines::class, 'to_slot_id');
    }

    public function storage_units()
    {
        return $this->hasMany(StorageUnits::class, 'current_slot_id');
    }

    public function currentStorageSummary()
    {
        return $this->hasMany(StorageUnits::class, 'current_slot_id')
            ->selectRaw('current_slot_id, part_id, SUM(qty) as qty')
            ->groupBy('current_slot_id', 'part_id');
    }
}
