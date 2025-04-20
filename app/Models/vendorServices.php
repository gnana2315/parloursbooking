<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorServices extends Model
{
    use HasFactory;
    /**
     * @var string $table
     */
    protected $table = 'vendor_services';
    protected $primaryKey = 'pbvs_id';
    /**
     * @var array $fillable
     */
    protected $fillable = [
        'pbvs_id',
        'pbvs_vendor_id',
        'pbvs_name',
        'pbvs_description',
        'pbvs_image',
        'pbvs_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
