<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class serviceFor extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'service_for';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbsf_id';
    protected $fillable = [
        'pbsf_id',
        'pbsf_name',
        'pbsf_icon',
        'pbsf_description',
        'pbsf_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
