<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Movements.
 *
 * @package namespace App\Models;
 */
class Movements extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doc_type',
        'movement_type',
        'from_warehouse_id',
        'to_warehouse_id',
        'created_by',
        'ref_no',
        'note'
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
        return $this->hasMany(MovementLines::class, 'movement_id');
    }

}
