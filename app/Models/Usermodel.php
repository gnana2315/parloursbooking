<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usermodel extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'pb_users';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbu_id', 'pbu_usertype', 'pbu_personid', 'pbu_name', 'pbu_email', 'pbu_email_verified_at', 'pbu_password', 'pbu_status', 'remember_token', 'created_at', 'updated_at'
    ];
}
