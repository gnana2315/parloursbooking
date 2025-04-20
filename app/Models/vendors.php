<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendors extends Model
{
    use HasFactory;
    /**
    * @var string $table
    */
    protected $table = 'vendor';
	protected $primaryKey = 'pbv_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbv_id',
        'pbv_servicefor',
        'pbv_businesstype',
        'pbv_business_category',
        'pbv_business_name',    
        'pbv_parlourcertificate',
        'pbv_brno',
        'pbv_brdoc',
        'pbv_police_report',
        'pbv_email',
        'pbv_contactno',
        'pbv_address',
        'pbv_city',
        'pbv_longatitude',
        'pbv_latitude',
        'pbv_accept_terms',
        'pbv_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
