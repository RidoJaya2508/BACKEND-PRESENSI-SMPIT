<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TelegramTemp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with('classGroup');

        if ($request->has('class_group_id') && $request->class_group_id !== null && $request->class_group_id !== '') {
            // Convert to integer to ensure type matching
            $classGroupId = (int) $request->class_group_id;
            $query->where('class_group_id', $classGroupId);
            
            Log::info('Fetching students for class_group_id', [
                'requested_id' => $request->class_group_id,
                'converted_id' => $classGroupId,
                'type' => gettype($classGroupId)
            ]);
        }

        if ($request->has('nis')) {
            $query->where('nis', $request->nis);
        }

        // Add pagination support
        $perPage = $request->input('per_page', 10);
        
        if ($request->has('per_page') && $request->per_page == 'all') {
            // Return all students without pagination for dropdowns
            $students = $query->get();
            return response()->json([
                'data' => $students,
            ], 200);
        }
        
        $students = $query->orderBy('name')->paginate($perPage);
        
        Log::info('Students fetched', [
            'count' => $students->count(),
            'total' => $students->total(),
            'class_group_id_filter' => $request->class_group_id ?? 'none'
        ]);

        return response()->json([
            'data' => $students->items(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nis' => 'required|string|unique:students',
            'parent_name' => 'nullable|string|max:255',
            'parent_telegram_id' => 'nullable|string|max:255',
            'class_group_id' => 'required|integer|exists:class_groups,id',
        ]);

        // Check for temporary Telegram connection
        $tempTelegram = TelegramTemp::where('token', $validated['nis'])->first();
        if ($tempTelegram) {
            $validated['parent_telegram_id'] = $tempTelegram->chat_id;
            $tempTelegram->delete();
        }

        $student = Student::create($validated);

        return response()->json([
            'data' => $student,
            'message' => 'Student created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $student = Student::with('classGroup')->findOrFail($id);
        return response()->json([
            'data' => $student,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nis' => 'required|string|unique:students,nis,' . $id,
            'parent_name' => 'nullable|string|max:255',
            'parent_telegram_id' => 'nullable|string|max:255',
            'class_group_id' => 'required|integer|exists:class_groups,id',
        ]);

        // Check for temporary Telegram connection
        $tempTelegram = TelegramTemp::where('token', $validated['nis'])->first();
        if ($tempTelegram) {
            $validated['parent_telegram_id'] = $tempTelegram->chat_id;
            $tempTelegram->delete();
        }

        $student->update($validated);

        return response()->json([
            'data' => $student,
            'message' => 'Student updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully',
        ], 200);
    }

    /**
     * Connect student to Telegram account.
     */
    public function connectTelegram(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|exists:students,nis',
            'telegram_id' => 'required|string',
        ]);

        $student = Student::where('nis', $validated['nis'])->firstOrFail();
        $student->parent_telegram_id = $validated['telegram_id'];
        $student->save();

        return response()->json([
            'data' => $student,
            'message' => 'Telegram connected successfully',
        ], 200);
    }

    /**
     * Check if a temporary telegram connection exists for a token (NIS).
     */
    public function checkTelegramStatus(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $temp = TelegramTemp::where('token', $request->token)->first();

        if ($temp) {
            Log::info('Telegram Status Check - Found', [
                'token' => $request->token,
                'chat_id' => $temp->chat_id
            ]);
            
            return response()->json([
                'connected' => true,
                'chat_id' => $temp->chat_id,
                'username' => $temp->username,
            ]);
        }

        Log::info('Telegram Status Check - Not Found', [
            'token' => $request->token
        ]);

        return response()->json([
            'connected' => false,
        ]);
    }
}
