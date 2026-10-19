<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    public const SLA_HOURS = ['URGENT' => 24, 'HIGH' => 72, 'MEDIUM' => 168, 'LOW' => 336];
    public const OPEN_STATUSES = ['PENDING', 'ASSIGNED', 'IN_PROGRESS'];

    protected $fillable = [
        'title', 'description', 'location',
        'priority', 'status', 'category_id', 'requester_id',
        'rating', 'rating_comment', 'rated_at', 'due_at', 'escalated_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'escalated_at' => 'datetime',
        'rated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $request) {
            $request->due_at ??= now()->addHours(self::SLA_HOURS[$request->priority ?? 'LOW'] ?? 336);
        });
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->due_at !== null && $this->due_at->isPast();
    }

    public function isAtRisk(): bool
    {
        return $this->isOpen() && ! $this->isOverdue() && $this->due_at !== null
            && now()->diffInHours($this->due_at, false) <= 12;
    }

    public function assignTo(User $officer, User $assignedBy, ?string $notes = null): Assignment
    {
        $assignment = $this->assignments()->create([
            'officer_id' => $officer->id,
            'assigned_by' => $assignedBy->id,
            'notes' => $notes,
        ]);

        $this->update(['status' => 'ASSIGNED']);

        Notification::create([
            'user_id' => $officer->id,
            'title' => 'New Assignment',
            'message' => "You have been assigned request #{$this->id}",
        ]);

        Notification::create([
            'user_id' => $this->requester_id,
            'title' => 'Request Update',
            'message' => "Your request #{$this->id} was assigned to {$officer->name}.",
        ]);

        AuditLog::create([
            'user_id' => $assignedBy->id,
            'action' => 'ASSIGN_REQUEST',
            'entity' => 'ServiceRequest',
            'entity_id' => $this->id,
        ]);

        return $assignment;
    }

    public function comments()
    {
        return $this->hasMany(RequestComment::class, 'request_id')->oldest();
    }

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
