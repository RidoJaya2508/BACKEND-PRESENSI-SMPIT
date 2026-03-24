<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'schedule_id',
        'student_id',
        'status',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function getStatusAttribute($value)
    {
        return $value === 'Ijin' ? 'Izin' : $value;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = $value === 'Ijin' ? 'Izin' : $value;
    }

    /**
     * Get the schedule that the attendance belongs to.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /**
     * Get the student that the attendance belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
