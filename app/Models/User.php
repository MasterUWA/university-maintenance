<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'phone'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    /** Officer with the fewest assigned or in-progress requests. */
    public static function leastBusyOfficer(): ?self
    {
        return static::role('OFFICER')
            ->withCount(['assignments as active_count' => fn ($assignments) => $assignments->whereHas(
                'request',
                fn ($request) => $request->whereIn('status', ['ASSIGNED', 'IN_PROGRESS'])
            )])
            ->orderBy('active_count')
            ->orderBy('id')
            ->first();
    }

    public function requestsMade()
    {
        return $this->hasMany(ServiceRequest::class, 'requester_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'officer_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
