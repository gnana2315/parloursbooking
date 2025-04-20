<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class services extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbs_id';
    protected $fillable = [
        'pbs_id',
        'pbs_vendor_id',
        'pbs_service_type',
        'pbs_service_for',
        'pbs_name',
        'pbs_description',
        'pbs_duration',
        'pbs_image',
        'pbs_price',
        'pbs_employees',
        'pbs_status',
        'created_at',
        'updated_at',
    ];
}
