<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorSpecialCloses extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'vendor_special_closes';
	protected $primaryKey = 'pbvsc_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbvsc_id',
        'pbvsc_vendor_id',
        'pbvsc_day',
        'pbvsc_full_day_closed',
        'pbvsc_from_time',
        'pbvsc_to_time',
        'pbvsc_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'pbvsc_from_time' => 'datetime:H:i',
        'pbvsc_to_time' => 'datetime:H:i',
        'pbvsc_full_day_closed' => 'boolean',
    ];
}
