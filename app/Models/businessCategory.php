<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class businessCategory extends Model
{
    use HasFactory;   
    
    /**
    * @var string $table
    */
    protected $table = 'business_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbbc_id';
    protected $fillable = [
        'pbbc_id',
        'pbbc_name',
        'pbbc_description',
        'pbbc_image',
        'pbbc_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
