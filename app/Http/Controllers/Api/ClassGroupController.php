<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        
        if ($request->has('per_page') && $request->per_page == 'all') {
            $classGroups = ClassGroup::with('homeroomTeacher')->get();
            $classGroups->map(function ($classGroup) {
                $classGroup->homeroom_teacher_name = $classGroup->homeroomTeacher ? $classGroup->homeroomTeacher->name : null;
                return $classGroup;
            });
            return response()->json(['data' => $classGroups], 200);
        }
        
        $classGroups = ClassGroup::with('homeroomTeacher')->orderBy('level')->orderBy('name')->paginate($perPage);
        
        $data = $classGroups->map(function ($classGroup) {
            $classGroup->homeroom_teacher_name = $classGroup->homeroomTeacher ? $classGroup->homeroomTeacher->name : null;
            return $classGroup;
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $classGroups->currentPage(),
                'last_page' => $classGroups->lastPage(),
                'per_page' => $classGroups->perPage(),
                'total' => $classGroups->total(),
                'from' => $classGroups->firstItem(),
                'to' => $classGroups->lastItem(),
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
            'level' => 'nullable|string|max:10',
            'homeroom_teacher_id' => 'nullable|integer|unique:class_groups,homeroom_teacher_id',
        ]);

        $classGroup = ClassGroup::create($validated);

        return response()->json([
            'data' => $classGroup,
            'message' => 'Class created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        return response()->json([
            'data' => $classGroup,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $classGroup = ClassGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:10',
            'homeroom_teacher_id' => 'nullable|integer|unique:class_groups,homeroom_teacher_id,' . $id,
        ]);

        $classGroup->update($validated);

        return response()->json([
            'data' => $classGroup,
            'message' => 'Class updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        $classGroup->delete();

        return response()->json([
            'message' => 'Class deleted successfully',
        ], 200);
    }

    /**
     * Sync students to their class groups
     * This will ensure all students have the correct class_group_id
     */
    public function syncStudents(Request $request, $id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        
        // Get all students that should belong to this class
        // For now, we'll sync students that don't have a class_group_id
        // or allow manual assignment via request
        $students = Student::whereNull('class_group_id')->get();
        
        if ($request->has('student_ids')) {
            // Assign specific students to this class
            $studentIds = $request->input('student_ids');
            Student::whereIn('id', $studentIds)
                ->update(['class_group_id' => $id]);
            
            return response()->json([
                'message' => 'Siswa berhasil disinkronkan ke kelas',
                'updated_count' => count($studentIds),
            ], 200);
        }
        
        // Return available students that can be assigned
        return response()->json([
            'data' => [
                'class' => $classGroup,
                'available_students' => $students,
                'current_students' => $classGroup->students,
            ],
        ], 200);
    }

    /**
     * Get sync status - shows students without classes and classes without students
     */
    public function getSyncStatus()
    {
        $studentsWithoutClass = Student::whereNull('class_group_id')->get();
        $classesWithoutStudents = ClassGroup::doesntHave('students')->get();
        $allClasses = ClassGroup::withCount('students')->get();

        return response()->json([
            'data' => [
                'students_without_class' => $studentsWithoutClass,
                'classes_without_students' => $classesWithoutStudents,
                'all_classes' => $allClasses->map(function($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'level' => $class->level,
                        'students_count' => $class->students_count,
                    ];
                }),
            ],
        ], 200);
    }
}
