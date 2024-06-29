<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class serviceCategory extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'servicecategory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbsc_id';
    protected $fillable = [
        'pbsc_id',
        'pbsc_name',
        'pbsc_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
