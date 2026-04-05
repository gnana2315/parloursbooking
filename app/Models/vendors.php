<?php

namespace App\Models;
use App\Models\User;
use App\Models\services;
use App\Models\vendorDocuments;
use App\Models\vendorStandardAvailability;
use App\Models\vendorConfig;
use App\Models\cities;
use App\Models\booking;
use App\Models\ratings;
use App\Models\vendorType;
use App\Models\serviceFor;
use App\Models\vendorSpecialCloses;
use App\Models\vendorPayoutItems;

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
        'pbv_display_name',    
        'pbv_documents',
        'pbv_brno',
        'pbv_email',
        'pbv_profile_image',
        'pbv_contactno',
        'pbv_address',
        'pbv_city',
        'pbv_longatitude',
        'pbv_latitude',
        'pbv_accept_terms',
        'pbv_staff_count',
        'pbv_status',
        'pbv_short_description',
        'pbv_therapist_service_area',
        'created_at',
        'updated_at',
        'deleted_at',
        'pbv_images',
        'pbv_first_name',
        'pbv_last_name',
        'pbv_gender',
        'pbv_dob'
    ];

    protected $casts = [
        'pbv_images' => 'array',
        'pbv_therapist_service_area' => 'array'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'pbu_vid', 'pbv_id');
    }

    public function services()
    {
        return $this->hasMany(services::class, 'pbs_vendor_id', 'pbv_id');
    }

    public function vendorDocuments()
    {
        return $this->hasMany(vendorDocuments::class, 'pbvd_vendor_id', 'pbv_id');
    }

    public function bankInfo()
    {
        return $this->hasMany(vendorBankInfo:: class, 'pbvb_id', 'pbv_id');
    }

    public function availability()
    {
        return $this->hasMany(vendorStandardAvailability::class, 'pbvsa_vendor_id', 'pbv_id');
    }

    public function config()
    {
        return $this->hasOne(vendorConfig::class, 'pbvc_vendorid', 'pbv_id');
    }

    public function city()
    {
        return $this->belongsTo(cities::class, 'pbv_city', 'pbc_cid');
    }

    public function booking(){
        return $this->hasMany(booking::class, 'pbb_vendor_id', 'pbv_id');
    }

    public function ratings(){
        return $this->hasMany(ratings::class, 'pbr_vendor_id', 'pbv_id');
    }

    public function vendorType(){
        return $this->belongsTo(vendorType::class, 'pbv_vendortype', 'pbvt_id');
    }

    public function serviceFor(){
        return $this->belongsTo(serviceFor::class, 'pbv_servicefor', 'pbsf_id');
    }

    public function specialCloses()
    {
        return $this->hasMany(vendorSpecialCloses::class, 'pbvsc_vendor_id', 'pbv_id');
    }

    public function vendorPayoutItems()
    {
        return $this->hasMany(vendorPayoutItems::class, 'pbvpi_vendor_id', 'pbv_id');
    }
}
