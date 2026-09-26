<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusUpdate extends Model
{
    protected $fillable = ['request_id', 'officer_id', 'status', 'comment'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
