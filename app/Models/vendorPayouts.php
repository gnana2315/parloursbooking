<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\vendors;

class vendorPayouts extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'vendor_payouts';
	protected $primaryKey = 'pbvp_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbvp_vendor_id',
        'pbvp_total_earned',
        'pbvp_total_paid',
        'pbvp_total_due',
        'pbvp_status',
        'created_at',
        'updated_at'
    ];

    /**
    * @var bool $timestamps
    */
    public $timestamps = true;
    
    public function vendors(){
        return $this->belongsTo(vendors::class, 'pbvp_vendor_id');
    }
    
}
