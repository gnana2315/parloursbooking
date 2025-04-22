<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    /**
    * @var string $table
    */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbu_id';
    protected $fillable = [
        'pbu_id',
        'pbu_usertype',
        'pbu_vid',
        'pbu_personid',
        'pbu_name',
        'pbu_email',
        'pbu_mobileno',
        'pbu_verification_token',
        'pbu_verification_token_expires_at',
        'pbu_email_verified_at',
        'pbu_mobileno_verified_at',
        'password',
        'pbu_first_name',
        'pbu_last_name',
        'pbu_dob',
        'pbu_gender',
        'pbu_address',
        'pbu_city',
        'pbu_nicno',
        'pbu_nic_doc',
        'pbu_accept_terms',
        'pbu_status',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pbu_email_verified_at' => 'datetime',
        'pbu_verification_token_expires_at' => 'datetime',
        'pbu_mobileno_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole($usertype)
    {
        return $this->getAttribute('pbu_usertype') == $usertype;
    }

    public function hasStatus($status)
    {
        return $this->getAttribute('pbu_status') == $status;
    }

    public function vendors(){
        return $this->hasOne(vendors::class, 'pbv_id', 'pbu_vid');
    }
}
