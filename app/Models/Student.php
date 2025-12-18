<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'name',
        'nis',
        'parent_name',
        'parent_telegram_id',
        'class_group_id',
    ];

    /**
     * Get the class group that the student belongs to.
     */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }
}
