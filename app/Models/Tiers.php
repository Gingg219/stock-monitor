<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Tiers.
 *
 * @package namespace App\Models;
 */
class Tiers extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rack_id',
        'level_no',
    ];
    
    public function slots()
    {
        return $this->hasMany(Slots::class, 'tier_id');
    }

    public function rack()
    {
        return $this->belongsTo(Racks::class, 'rack_id');
    }
}
