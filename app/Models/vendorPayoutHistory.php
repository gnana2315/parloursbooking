<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\vendors;
use App\Models\vendorPayouts;

class vendorPayoutHistory extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'vendor_payout_history';
	protected $primaryKey = 'pbvph_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbvph_payout_id',
        'pbvph_vendor_id',
        'pbvph_amount',
        'pbvph_payment_method',
        'pbvph_reference',
        'pbvph_description',
        'pbvph_status',
        'created_at',
        'updated_at'
    ];

    public function vendors(){
        return $this->belongsTo(vendors::class, 'pbvph_vendor_id');
    }

    public function payout(){
        return $this->belongsTo(vendorPayouts::class, 'pbvph_payout_id');
    }
}
