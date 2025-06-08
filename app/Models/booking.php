<?php

namespace App\Models;
use App\Models\bookingDetail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'bookings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbb_id';
    protected $fillable = [
        'pbb_id',
        'pbb_vendor_id',
        'pbb_customer_id',
        'pbb_promo_id',
        'pbb_booking_details',
        'pbb_booking_date',
        'pbb_booking_duration',
        'pbb_booking_start_time',
        'pbb_booking_end_time',
        'pbb_ref_no',
        'pbb_type',
        'pbb_service_location',
        'pbb_contact_no',
        'pbb_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function bookingDetails(){
        return $this->hasMany(bookingDetail::class, 'pbbd_booking_id', 'pbb_id');
    }
}
