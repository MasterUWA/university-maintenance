<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'title', 'description', 'location',
        'priority', 'status', 'category_id', 'requester_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'request_id');
    }

    public function statusUpdates()
    {
        return $this->hasMany(StatusUpdate::class, 'request_id')->latest();
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'request_id');
    }
}
