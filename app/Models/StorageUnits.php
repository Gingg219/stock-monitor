<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class StorageUnits.
 *
 * @package namespace App\Models;
 */
class StorageUnits extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_type',
        'unit_code',
        'income_line_id',
        'part_id',
        'lot_no',
        'expiry_date',
        'qty',
        'warehouse_id',
        'current_slot_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function movement_lines()
    {
        return $this->hasMany(MovementLines::class, 'storage_unit_id');
    }

    public function part()
    {
        return $this->belongsTo(Parts::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slots::class, 'current_slot_id');
    }

    public function income_lines()
    {
        return $this->belongsTo(IncomeLines::class, 'income_line_id');
    }
}