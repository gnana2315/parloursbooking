<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'notifications';

    /**
    * @var array $fillable
    */
	protected $primaryKey = 'pbn_id';
    protected $fillable = [
        'pbn_id',
        'pbn_user_id',
        'pbn_title',
        'pbn_message',
        'pbn_is_read',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
