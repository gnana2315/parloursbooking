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
        'pbpbd_id',
        'pbpbi_btach_id',
        'pbpbi_vendor_payout_item_id',
        'pbpbi_status'
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
