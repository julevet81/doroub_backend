<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;


class RoleController extends Controller
{
    // عرض جميع الأدوار
    public function index()
    {
        if (!Auth::user()->can('أدارة الأدوار')) {
            abort(403, 'غير مصرح لك');
        }

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
            //'الوصف' => $role->description,
            'الصلاحيات' => $role->permissions->pluck('name')
        ];

        return response()->json([
            'data' => $roleData
        ], 200);
    }

    // إنشاء دور جديد
    public function store(Request $request)
    {
        if (!Auth::user()->can('إضافة أدوار')) {
            abort(403, 'غير مصرح لك');
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api'
            ]);

            if ($request->filled('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الدور بنجاح',
                'role' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // تحديث دور
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->name
            ]);

            
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث الدور بنجاح',
                'role' => $role->load('permissions')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // حذف دور
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'message' => 'تم حذف الدور بنجاح'
        ], 200);
    }
}
