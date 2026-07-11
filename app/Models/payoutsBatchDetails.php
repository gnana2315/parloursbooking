<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\payoutsBatch;

class payoutsBatchDetails extends Model
{
    use HasFactory;

    protected $table = 'payouts_batch_details';

	protected $primaryKey = 'pbpbd_id';

    protected $fillable = [
        'pbpbi_id',
        'pbpbi_batch_id',
        'pbpbi_vendor_id',
        'pbpbi_vendor_payout_item_id',
        'pbpbi_paid_date',
        'pbpbi_paid_ref_no',
        'pbpbi_paid_by',
        'pbpbi_paid_slip_url',
        'pbpbi_remarks',
        'pbpbi_status'
    ];

    protected $casts = [
        'pbpbi_paid_date' => 'date',
    ];

    public function payoutBatchDetails()
    {
        return $this->belongsTo(payoutsBatch::class, 'pbpbi_btach_id', 'id');
    }

    public function vendorPayoutItem()
    {
        return $this->belongsTo(vendorPayoutItems::class, 'pbpbi_vendor_payout_item_id', 'pbvpi_id');
    }
}
