<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendors extends Model
{
    use HasFactory;
    /**
    * @var string $table
    */
    protected $table = 'vendor';
	protected $primaryKey = 'pbv_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbv_id',
        'pbv_tenentid',
        'pbv_servicefor',
        'pbv_vendortype',
        'pbv_business_category',
        'pbv_business_name',    
        'pbv_documents',
        'pbv_brno',
        'pbv_email',
        'pbv_contactno',
        'pbv_address',
        'pbv_city',
        'pbv_longatitude',
        'pbv_latitude',
        'pbv_accept_terms',
        'pbv_status',
        'created_at',
        'updated_at',
        'deleted_at',
        'pbv_images'
    ];

    protected $casts = [
        'pbv_images' => 'array',
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(services::class, 'pbs_vendor_id', 'pbv_id');
    }
}
