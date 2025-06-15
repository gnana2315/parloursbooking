<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class deviceToken extends Model
{
    use HasFactory;
    /**
    * @var string $table
    */
    protected $table = 'device_tokens';

    /**
    * @var array $fillable
    */
	protected $primaryKey = 'pbdt_id';
    protected $fillable = [
        'pbdt_user_id',
        'pbdt_device_token',
        'created_at',
        'updated_at',
    ];
}
