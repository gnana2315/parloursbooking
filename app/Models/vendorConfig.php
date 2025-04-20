<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorConfig extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'vendor_config';
	protected $primaryKey = 'pbvc_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbvc_id',
        'pbvc_vendor_id',
        'pbvc_display_name',
        'pbvc_logo',
        'pbvc_service_at_time',
        'pbvc_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
