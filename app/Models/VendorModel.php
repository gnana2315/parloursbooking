<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorModel extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'pb_vendor';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbv_id', 'pbv_servicetype', 'pbv_name', 'pbv_logo', 'pbv_parlourcertificate', 'pbv_brno', 'pbv_brdoc', 'pbv_email', 'pbv_contactno', 'pbv_address', 'pbv_city', 'pbp_status', 'created_at', 'updated_at', 'deleted_at'
    ];
}
