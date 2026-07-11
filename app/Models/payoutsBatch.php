<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payoutsBatch extends Model
{
    use HasFactory;

    protected $table = 'payouts_batch';

	protected $primaryKey = 'pbpb_id';

    protected $fillable = [
        'pbpb_id',
        'pbpb_batch_no',
        'pbpb_batch_name',
        'pbpb_total_amount',
        'pbpb_total_payouts',
        'pbpb_batch_valid_date',
        'pbpb_notes',
        'pbpb_status',
        'pbpb_created_by',
        'pbpb_updated_by',
    ];

    protected $casts = [
        'pbpb_batch_valid_date' => 'date',
    ];
}
