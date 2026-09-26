<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['request_id', 'officer_id', 'assigned_by', 'notes'];
    protected $casts = ['assigned_at' => 'datetime'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
