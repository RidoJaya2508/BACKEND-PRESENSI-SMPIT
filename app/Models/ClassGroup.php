<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassGroup extends Model
{
    protected $fillable = [
        'name',
        'level',
        'homeroom_teacher_id',
    ];

    /**
     * Get the homeroom teacher (user) for the class group.
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    /**
     * Get the students in the class group.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_group_id');
    }

    /**
     * Get the schedules for the class group.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_group_id');
    }
}
