<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\vendors;

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
        'pbvd_document_extra',
        'created_at',
        'updated_at'
    ];

    public function vendor()
    {
        return $this->belongsTo(vendors::class, 'pbvd_vendor_id', 'pbv_id');
    }
}
