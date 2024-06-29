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
        'pbv_servicetype',
        'pbv_name',
        'pbv_logo',
        'pbv_parlourcertificate',
        'pbv_brno',
        'pbv_brdoc',
        'pbv_email',
        'pbv_contactno',
        'pbv_address',
        'pbv_city',
        'pbp_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
