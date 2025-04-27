<?php

namespace App\Models;
use App\Models\booking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bookingDetail extends Model
{
    use HasFactory;      
    
    /**
    * @var string $table
    */
    protected $table = 'booking_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbbd_id';
    protected $fillable = [
        'pbbd_id',
        'pbbd_booking_id',
        'pbbd_service_id',
        'pbbd_employee_id',
        'pbbd_promo_id',
        'pbbd_amount',
        'pbbd_discount',
        'pbbd_total_amount',
        'pbb_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function booking()
    {
        return $this->belongsTo(booking::class);
    }
}
