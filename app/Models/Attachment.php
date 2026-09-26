<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['request_id', 'path', 'original_name'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }
}
