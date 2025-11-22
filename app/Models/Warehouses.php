<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Warehouses.
 *
 * @package namespace App\Models;
 */
class Warehouses extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'mode',
        'is_active',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function racks()
    {
        return $this->hasMany(Racks::class, 'warehouse_id');
    }

    public function fixed_locations()
    {
        return $this->hasMany(FixedLocations::class, 'warehouse_id');
    }

    public function movements1()
    {
        return $this->hasMany(Movements::class, 'from_warehouse_id');
    }

    public function movements2()
    {
        return $this->hasMany(Movements::class, 'to_warehouse_id');
    }

    public function stock_ledger()
    {
        return $this->hasMany(StockLedger::class, 'warehouse_id');
    }

    public function storage_units()
    {
        return $this->hasMany(StorageUnits::class, 'warehouse_id');
    }
}
