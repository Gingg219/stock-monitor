<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class IncomeLines.
 *
 * @package namespace App\Models;
 */
class IncomeLines extends Model implements Transformable
{
    use TransformableTrait;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'income_id',
        'part_id',
        'vendor_id',
        'lot_no',
        'expiry_date',
        'qty_total',
        'snp_overwrite',
        'remark',
    ];
    public function storage_units()
    {
        return $this->hasMany(StorageUnits::class, 'income_line_id');
    }

    public function parts()
    {
        return $this->belongsTo(Parts::class, 'part_id');
    }

    public function vendors()
    {
        return $this->belongsTo(Vendors::class, 'vendor_id');
    }

    public function income()
    {
        return $this->belongsTo(Incomes::class, 'income_id');
    }

}
