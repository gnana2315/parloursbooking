<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class seo_key_words extends Model
{
    use HasFactory;

    /**
    * @var string $table
    */
    protected $table = 'seo_key_words';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbseo_id';
    protected $fillable = [
        'pbseo_id',
        'pbseo_page',
        'pbseo_words',
        'pbseo_status',
        'created_at',
        'updated_at',
    ];
}
