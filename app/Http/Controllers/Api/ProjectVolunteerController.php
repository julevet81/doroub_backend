<?php

namespace App\Http\Controllers;

use App\Models\ProjectVolunteer;
use Illuminate\Http\Request;

class ProjectVolunteerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => ProjectVolunteer::all()
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'volunteer_id' => 'required|exists:volunteers,id',
            'position' => 'nullable|string|max:255',
        ]);

        $projectVolunteer = ProjectVolunteer::create($validated);

        return response()->json([
            'message' => 'تم إضافة المتطوع إلى المشروع بنجاح.',
            'data' => $projectVolunteer
        ], 201);
    }

    public function show(ProjectVolunteer $projectVolunteer)
    {
        return response()->json([
            'data' => $projectVolunteer
        ], 200);
    }

    public function update(Request $request, ProjectVolunteer $projectVolunteer)
    {
        $validated = $request->validate([
            'position' => 'nullable|string|max:255',
        ]);

        $projectVolunteer->update($validated);

        return response()->json([
            'message' => 'تم تحديث المتطوع في المشروع بنجاح.',
            'data' => $projectVolunteer
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectVolunteer $projectVolunteer)
    {
        $projectVolunteer->delete();

        return response()->json([
            'message' => 'تم حذف المتطوع من المشروع بنجاح.'
        ], 200);
    }
}
