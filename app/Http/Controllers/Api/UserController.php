<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(['data' => User::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['teacher', 'admin', 'homeroom', 'HOMEROOM', 'TEACHER', 'ADMIN'])],
        ]);

        // Normalize role to lowercase
        if (isset($validated['role'])) {
            $validated['role'] = strtolower($validated['role']);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json([
            'data' => $user,
            'message' => 'User berhasil dibuat'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => 'sometimes|string|min:8',
            'role' => ['sometimes', 'required', Rule::in(['teacher', 'admin', 'homeroom', 'HOMEROOM', 'TEACHER', 'ADMIN'])],
        ]);

        // Normalize role to lowercase
        if (isset($validated['role'])) {
            $validated['role'] = strtolower($validated['role']);
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'data' => $user,
            'message' => 'User berhasil diperbarui'
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting user if they are assigned as homeroom teacher
        $isHomeroomTeacher = ClassGroup::where('homeroom_teacher_id', $id)->exists();
        if ($isHomeroomTeacher) {
            return response()->json([
                'message' => 'Tidak dapat menghapus user yang sedang menjadi wali kelas'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ]);
    }

    public function getAvailableHomeroomTeachers()
    {
        $assignedTeacherIds = ClassGroup::whereNotNull('homeroom_teacher_id')->pluck('homeroom_teacher_id');
        
        $availableTeachers = User::where('role', 'teacher')
                                 ->whereNotIn('id', $assignedTeacherIds)
                                 ->get();

        return response()->json(['data' => $availableTeachers]);
    }
}
