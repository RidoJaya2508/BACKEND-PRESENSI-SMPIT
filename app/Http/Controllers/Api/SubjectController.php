<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => Subject::all(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:subjects',
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'data' => $subject,
            'message' => 'Subject created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subject = Subject::findOrFail($id);
        return response()->json([
            'data' => $subject,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:subjects,code,' . $id,
        ]);

        $subject->update($validated);

        return response()->json([
            'data' => $subject,
            'message' => 'Subject updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return response()->json([
            'message' => 'Subject deleted successfully',
        ], 200);
    }
}
