<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $fillable = [
        'subject_id',
        'teacher_id',
        'class_group_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the subject that the schedule belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the teacher (user) that the schedule belongs to.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the class group that the schedule belongs to.
     */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }

    /**
     * Get the attendances for the schedule.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'schedule_id');
    }

    /**
     * Accessor for start_time
     */
    protected function startTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('H:i') : null,
        );
    }

    /**
     * Accessor for end_time
     */
    protected function endTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('H:i') : null,
        );
    }
}
