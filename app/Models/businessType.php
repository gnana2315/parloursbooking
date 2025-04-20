<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class businessType extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'business_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbbt_id';
    protected $fillable = [
        'pbbt_id',
        'pbbt_name',
        'pbbt_icon',
        'pbbt_description',
        'pbbt_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
