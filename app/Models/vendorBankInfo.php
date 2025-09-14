<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorBankInfo extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'vendor_bank_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbvb_id';
    protected $fillable = [
        'pbvb_id',
        'pbvb_vendorid',
        'pbvb_bankname',
        'pbvb_holder_name',
        'pbvb_branch',
        'pbvb_branch_code',
        'pbvb_accountno',
        'pbvb_is_active',
        'pbvb_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
