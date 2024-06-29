<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userLogs extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'userlogs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbul_id';
    protected $fillable = [
        'pbu_id',
        'pbul_description',
        'pbul_time',
        'pbul_status',
        'created_at',
        'updated_at',
    ];
}
