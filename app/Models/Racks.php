<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Racks.
 *
 * @package namespace App\Models;
 */
class Racks extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'code',
    ];

    public function tiers()
    {
        return $this->hasMany(Tiers::class, 'rack_id');
    }
}
