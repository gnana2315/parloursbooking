<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\booking;

class promoCode extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'promo_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbpc_id';
    protected $fillable = [
        'pbpc_id',
        'pbpc_name',
        'pbpc_code',
        'pbpc_discount_type',
        'pbpc_value',
        'pbpc_discount',
        'pbpc_max_discount',
        'pbpc_start_date',
        'pbpc_days',
        'pbpc_end_date',
        'pbpc_min_booking_amount',
        'pbpc_uses_count',
        'pbpc_description',
        'pbpc_image',
        'pbpc_status',
        'created_at',
        'updated_at',
        'deleted_at',
        'pbpc_promo_types',
        'pbpc_vendor_share',
        'pbpc_platform_share',
    ];

    protected $casts = [
        'pbpc_vendor_ids' => 'array',
        'pbpc_service_ids' => 'array',
        'pbpc_vendor_service_map' => 'array',
    ];

    public function bookings() {
        return $this->hasMany(booking::class, 'pbpc_id', 'pbb_promo_id');
    }
}
