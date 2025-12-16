<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectAssistance;
use Illuminate\Http\Request;

class ProjectAssistanceController extends Controller
{
    
    public function index()
    {
        return response()->json([
            'data' => ProjectAssistance::all()
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assistance_item_id' => 'required|exists:assistance_items,id',
            'quantity' => 'required|numeric',
        ]);

        $projectAssistance = ProjectAssistance::create($validated);

        return response()->json([
            'message' => 'تم إنشاء مساعدات المشروع بنجاح',
            'data' => $projectAssistance
        ], 201);
    }

    public function show(ProjectAssistance $projectAssistance)
    {
        return response()->json([
            'data' => $projectAssistance
        ], 200);
    }

    public function update(Request $request, ProjectAssistance $projectAssistance)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assistance_item_id' => 'required|exists:assistance_items,id',
            'quantity' => 'required|numeric',
        ]);

        $projectAssistance->update($validated);

        return response()->json([
            'message' => 'تم تحديث مساعدات المشروع بنجاح',
            'data' => $projectAssistance
        ], 200);
    }

    public function destroy(ProjectAssistance $projectAssistance)
    {
        $projectAssistance->delete();

        return response()->json([
            'message' => 'تم حذف مساعدات المشروع بنجاح'
        ], 200);
    }
}
