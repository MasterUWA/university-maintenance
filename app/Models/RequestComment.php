<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestComment extends Model
{
    protected $fillable = ['request_id', 'user_id', 'body', 'is_internal'];
    protected $casts = ['is_internal' => 'boolean'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
