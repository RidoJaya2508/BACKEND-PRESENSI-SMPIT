<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        
        $schedules = Schedule::with(['subject', 'teacher', 'classGroup'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate($perPage);

        $data = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'subject_id' => $schedule->subject_id,
                'subject_name' => $schedule->subject->name ?? '',
                'teacher_id' => $schedule->teacher_id,
                'teacher_name' => $schedule->teacher->name ?? '',
                'class_group_id' => $schedule->class_group_id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'room' => $schedule->room,
                'is_active' => $schedule->is_active,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
                'from' => $schedules->firstItem(),
                'to' => $schedules->lastItem(),
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|integer',
            'teacher_id' => 'nullable|integer',
            'class_group_id' => 'required|integer|exists:class_groups,id',
            'day_of_week' => 'required|integer',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'room' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Check for schedule conflicts
        $conflict = $this->getConflictingSchedule($request);
        if ($conflict) {
            $conflict->load('subject');
            $subjectName = $conflict->subject->name ?? 'Unknown Subject';
            return response()->json([
                'message' => "Jadwal bentrok dengan: {$subjectName} ({$conflict->start_time} - {$conflict->end_time})",
            ], 422);
        }

        $schedule = Schedule::create($validated);

        return response()->json([
            'data' => $schedule,
            'message' => 'Schedule created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $schedule = Schedule::findOrFail($id);
        return response()->json([
            'data' => $schedule,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $validated = $request->validate([
            'subject_id' => 'required|integer',
            'teacher_id' => 'nullable|integer',
            'class_group_id' => 'required|integer|exists:class_groups,id',
            'day_of_week' => 'required|integer',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'room' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Check for schedule conflicts (excluding current schedule)
        $conflict = $this->getConflictingSchedule($request, $id);
        if ($conflict) {
            $conflict->load('subject');
            $subjectName = $conflict->subject->name ?? 'Unknown Subject';
            return response()->json([
                'message' => "Jadwal bentrok dengan: {$subjectName} ({$conflict->start_time} - {$conflict->end_time})",
            ], 422);
        }

        $schedule->update($validated);

        return response()->json([
            'data' => $schedule,
            'message' => 'Schedule updated successfully',
        ], 200);
    }

    /**
     * Check for overlapping schedules and return the conflicting schedule if any.
     */
    private function getConflictingSchedule(Request $request, $excludeId = null)
    {
        $query = Schedule::where('class_group_id', $request->class_group_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully',
        ], 200);
    }
}
