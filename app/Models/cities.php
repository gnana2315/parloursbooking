<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cities extends Model
{
    use HasFactory;
    /**
    * @var string $table
    */
    protected $table = 'cities';

    /**
    * @var array $fillable
    */
	protected $primaryKey = 'pbc_cid';
    protected $fillable = [
        'pbc_cid',
        'pbc_cityname',
        'pbc_cstatus',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
