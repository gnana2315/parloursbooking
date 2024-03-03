<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class person extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'persons';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbp_id', 'pbv_id', 'pbp_intial', 'pbp_firstname', 'pbp_lastname', 'pbp_nicno', 'pbp_contactno', 'pbp_email', 'pbp_address', 'pbp_status', 'created_at', 'updated_at', 'deleted_at'
    ];
}
