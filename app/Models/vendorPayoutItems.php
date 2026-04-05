<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\booking;
use App\Models\paymentTransection;
use App\Models\vendorPayouts;
use App\Models\vendorPayoutHistory;
use App\Models\vendors;

class vendorPayoutItems extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'vendor_payout_items';
	protected $primaryKey = 'pbvpi_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbvpi_payout_id',
        'pbvpi_booking_id',
        'pbvpi_payment_id',
        'pbvpi_vendor_id',
        'pbvpi_amount',
        'pbvpi_platform_fee',
        'pbvpi_vendor_amount',
        'pbvpi_status',
        'pbvpi_payout_history_id',
        'created_at',
        'updated_at'
    ];

    public function booking(){
        return $this->belongsTo(booking::class, 'pbvpi_booking_id');
    }

    public function payment(){
        return $this->belongsTo(paymentTransection::class, 'pbvpi_payment_id');
    }

    public function payout(){
        return $this->belongsTo(vendorPayouts::class, 'pbvpi_payout_id');
    }

    public function payoutHistory(){
        return $this->belongsTo(vendorPayoutHistory::class, 'pbvpi_payout_history_id');
    }

    public function vendor(){
        return $this->belongsTo(vendors::class, 'pbvpi_vendor_id');
    }
}
