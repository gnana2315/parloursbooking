<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorStandardAvailability extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'vendor_standard_availability';
	protected $primaryKey = 'pbvsa_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbvsa_id',
        'pbvsa_vendor_id',
        'pbvsa_day',
        'pbvsa_start_time',
        'pbvsa_end_time',
        'pbvsa_is_open',
        'pbvsa_status',
        'created_at',
        'updated_at',
        'deleted_at',
        'pbvsa_isEdit'
    ];

    protected $casts = [
        'pbvsa_start_time' => 'datetime:H:i',
        'pbvsa_end_time' => 'datetime:H:i',
        'pbvsa_is_open' => 'boolean',
        'pbvsa_isEdit' => 'boolean',
    ];
}
