<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class requiredDocument extends Model
{
    use HasFactory;
    /**
    * @var string $table
    */
    protected $table = 'required_document';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $primaryKey = 'pbrd_id';
    protected $fillable = [
        'pbrd_id',
        'pbrd_vendor_type',
        'pbrd_name',
        'pbrd_label',
        'pbrd_is_single',
        'pbrd_required',
        'pbrd_status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'pbrd_is_single' => 'boolean',
        'pbrd_required' => 'boolean',
        'pbrd_status' => 'boolean',
    ];
}
