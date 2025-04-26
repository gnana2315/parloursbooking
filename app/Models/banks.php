<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class banks extends Model
{
    use HasFactory;    
    
    /**
    * @var string $table
    */
    protected $table = 'banks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbb_id';
    protected $fillable = [
        'pbb_id',
        'pbb_name',
        'pbb_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
