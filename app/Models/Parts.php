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

}
