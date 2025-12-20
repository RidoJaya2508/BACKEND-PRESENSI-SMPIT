<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Eager load relationships needed for the report
        $query = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup']);

        // Filter by date range on the attendance record itself
        if ($request->has('start_date') && $request->has('end_date')) {
            // Parse dates in the application timezone (Asia/Jakarta)
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            
            $query->whereBetween('recorded_at', [$startDate, $endDate]);
        }

        // Filter by class, via the schedule relationship
        if ($request->has('class_id')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('class_group_id', $request->class_id);
            });
        }
    
        // Filter by subject, via the schedule relationship
        if ($request->has('subject_id')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        // Keep existing filters
        if ($request->has('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        return response()->json([
            'data' => $query->get(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|integer',
            'student_id' => 'required|integer',
            'status' => 'required|string|in:Hadir,Terlambat,Izin,Sakit,Alpa,Bolos',
            'recorded_at' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $attendance = Attendance::create($validated);
        $attendance->load(['student', 'schedule.classGroup', 'schedule.subject']);

        // Send Telegram Notification
        if ($attendance->student && $attendance->student->parent_telegram_id) {
            $startTime = \Carbon\Carbon::parse($attendance->schedule->start_time)->format('H:i');
            $endTime = \Carbon\Carbon::parse($attendance->schedule->end_time)->format('H:i');
            $scheduleTime = "{$startTime} - {$endTime}";
            
            // Use current server time (Jakarta) for the notification date
            // This ensures the date matches the actual day the attendance was marked
            $date = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y');

            $this->telegramService->sendNotification(
                $attendance->student->parent_telegram_id,
                $attendance->student->name,
                $attendance->schedule->classGroup->name ?? '-',
                $attendance->schedule->subject->name ?? '-',
                $attendance->status,
                $scheduleTime,
                $date
            );
        }

        return response()->json([
            'data' => $attendance,
            'message' => 'Attendance recorded successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $attendance = Attendance::with(['student', 'schedule'])->findOrFail($id);
        return response()->json([
            'data' => $attendance,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:Hadir,Terlambat,Izin,Sakit,Alpa,Bolos',
            'recorded_at' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $attendance->update($validated);
        $attendance->load(['student', 'schedule.classGroup', 'schedule.subject']);

        // Send Telegram Notification (Optional: You might want to limit this to status changes only)
        if ($attendance->student && $attendance->student->parent_telegram_id) {
            $startTime = \Carbon\Carbon::parse($attendance->schedule->start_time)->format('H:i');
            $endTime = \Carbon\Carbon::parse($attendance->schedule->end_time)->format('H:i');
            $scheduleTime = "{$startTime} - {$endTime}";
            
            // Use current server time (Jakarta) for the notification date
            $date = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y');

            $this->telegramService->sendNotification(
                $attendance->student->parent_telegram_id,
                $attendance->student->name,
                $attendance->schedule->classGroup->name ?? '-',
                $attendance->schedule->subject->name ?? '-',
                $attendance->status,
                $scheduleTime,
                $date
            );
        }

        return response()->json([
            'data' => $attendance,
            'message' => 'Attendance updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'message' => 'Attendance deleted successfully',
        ], 200);
    }
}
