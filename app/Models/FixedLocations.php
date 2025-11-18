<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class FixedLocations.
 *
 * @package namespace App\Models;
 */
class FixedLocations extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'part_id',
        'slot_id',
        'min_qty',
        'max_qty',
    ];

    public function parts()
    {
        return $this->belongsTo(Parts::class, 'part_id');
    }
}
