<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorType extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'vendor_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbvt_id';
    protected $fillable = [
        'pbvt_id',
        'pbvt_name',
        'pbvt_icon',
        'pbvt_description',
        'pbvt_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
