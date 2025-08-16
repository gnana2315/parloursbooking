<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'customer';

    /**
    * @var array $fillable
    */
	protected $primaryKey = 'pbc_id';
    protected $fillable = [
        'pbc_id',
        'pbc_user_id',
        'pbc_initial',
        'pbc_first_name',
        'pbc_last_name',
        'pbc_dob',
        'pbc_nic_no',
        'pbc_nic_document',
        'pbc_sex',
        'pbc_address',
        'pbc_city',
        'pbc_email',
        'pbc_contact_no',
        'pbc_profile_image',
        'pbc_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'pbc_fav' => 'array',
    ];

    public function bookings()
    {
        return $this->hasMany(booking::class, 'pbb_customer_id', 'pbc_id');
    }
}
