<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Vendors.
 *
 * @package namespace App\Models;
 */
class Vendors extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name'
    ];

    public function parts()
    {
        return $this->hasMany(Parts::class, 'vendor_id');
    }

    public function income_lines()
    {
        return $this->hasMany(IncomeLines::class, 'vendor_id');
    }
}
