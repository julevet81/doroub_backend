<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // عرض جميع الأدوار
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json([
            'message' => 'تم جلب جميع الأدوار بنجاح',
            'data' => $roles
        ], 200);
    }

    // عرض دور واحد
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        $roleData = [
            'الاسم' => $role->name,
            'الوصف' => $role->description,
            'الصلاحيات' => $role->permissions->pluck('name')
        ];

        return response()->json([
            'data' => $roleData
        ], 200);
    }

    // إنشاء دور جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'تم إنشاء الدور بنجاح',
            'data' => $role
        ], 201);
    }

    // تحديث دور
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array'
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->name;
        $role->description = $request->description;
        $role->save();

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return response()->json([
            'message' => 'تم تحديث الدور بنجاح',
            'data' => $role
        ], 200);
    }

    // حذف دور
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'message' => 'تم حذف الدور بنجاح'
        ], 200);
    }
}
