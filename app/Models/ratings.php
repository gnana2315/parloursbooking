<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ratings extends Model
{
    use HasFactory;
    /**
    * @var string $table
    */
    protected $table = 'ratings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbr_id';
    protected $fillable = [
        'pbr_id',
        'pbr_vendor_id',
        'pbr_booking_id',
        'pbr_customer_id',
        'pbr_rating',
        'pbr_comments',
        'pbr_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
