<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorBusinessCategory extends Model
{
    use HasFactory;
    /**
     * @var string $table
     */
    protected $table = 'vendor_business_category';
    protected $primaryKey = 'pbbc_id';
    /**
     * @var array $fillable
     */
    protected $fillable = [
        'pbbc_id',
        'pbbc_vendor_id',
        'pbbc_name',
        'pbbc_description',
        'pbbc_image',
        'pbbc_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
