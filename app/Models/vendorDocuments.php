<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendorDocuments extends Model
{
    use HasFactory;
    
    /**
    * @var string $table
    */
    protected $table = 'vendor_documents';
	protected $primaryKey = 'pbvd_id';

    /**
    * @var array $fillable
    */
    protected $fillable = [
        'pbvd_id',
        'pbvd_vendor_id',
        'pbvd_required_document_id',
        'pbvd_document_name',
        'pbvd_document_url',
        'pbvd_document_status',
        'created_at',
        'updated_at'
    ];
}
