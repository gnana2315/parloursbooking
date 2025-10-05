<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paymentTransection extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'payment_transection';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbpt_id',
        'pbpt_transaction_id',
        'pbpt_booking_id',
        'pbpt_vendor_id',
        'pbpt_customer_id',
        'pbpt_payment_method',
        'pbpt_total_amount',
        'pbpt_discount_amount',
        'pbpt_final_amount',
        'pbpt_platform_fee',
        'pbpt_vendor_amount',
        'pbpt_payment_response',
        'pbpt_payment_ref_no',
        'pbpt_description',
        'pbpt_status',
        'pbpt_remarks',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $primaryKey = 'pbpt_id';

    public function booking() {
        return $this->belongsTo(booking::class, 'pbpt_booking_id', 'pbb_id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'pbpt_customer_id', 'pbc_id');
    }

    public function vendor(){
        return $this->belongsTo(vendors::class, 'pbpt_vendor_id', 'pbv_id');
    }

    public function payoutItems(){
        return $this->hasMany(vendorPayoutItems::class, 'pbvpi_payment_id', 'pbpt_id');
    }
}
