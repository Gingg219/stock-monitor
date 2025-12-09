<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Incomes.
 *
 * @package namespace App\Models;
 */
class Incomes extends Model implements Transformable
{
    use TransformableTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'income_no',
        'invoice_no',
        'received_at',
        'note',
        'created_by'
    ];

    protected $appends = [
        'created_at_display',
        'updated_at_display',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

      // accessor trả dạng "YYYY-MM-DD HH:mm:ss"
    public function getCreatedAtDisplayAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    }

    public function getUpdatedAtDisplayAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
    }

    public function income_lines()
    {
        return $this->hasMany(IncomeLines::class, 'income_id');
    }

}
