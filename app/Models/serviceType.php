<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class serviceType extends Model
{
    use HasFactory;
     /**
    * @var string $table
    */
    protected $table = 'servicetype';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbst_id';
    protected $fillable = [
        'pbst_id',
        'pbst_name',
        'pbst_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
