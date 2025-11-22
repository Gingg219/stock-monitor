<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Parts.
 *
 * @package namespace App\Models;
 */
class Parts extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_no',
        'name',
        'vendor_id',
        'snp',
        'has_expiry',
        'expiry_days',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function stock_ledger()
    {
        return $this->hasMany(StockLedger::class, 'part_id');
    }

    public function storage_units()
    {
        return $this->hasMany(StorageUnits::class, 'part_id');
    }

    public function fixed_locations()
    {
        return $this->hasMany(FixedLocations::class, 'part_id');
    }

    public function income_lines()
    {
        return $this->hasMany(IncomeLines::class, 'part_id');
    }

    public function movement_lines()
    {
        return $this->hasMany(MovementLines::class, 'part_id');
    }
}
