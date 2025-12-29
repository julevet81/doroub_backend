<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Auth as SupportFacadesAuth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // عرض جميع المستخدمين
    public function index()
    {

        if (!Auth::user() || !Auth::user()->can('ادارة المستخدمين')){
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $users = User::all();
        return response()->json([
            'message' => 'تم جلب جميع المستخدمين بنجاح',
            'data' => $users
        ], 200);
    }

    // عرض مستخدم واحد
    public function show($id)
    {
        if (!Auth::user() || !Auth::user()->can('عرض مستخدم')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $user = User::findOrFail($id);
        return response()->json([
            'message' => 'تم جلب بيانات المستخدم بنجاح',
            'data' => $user
        ], 200);
    }

    // إنشاء مستخدم جديد
    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('إضافة مستخدمين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء المستخدم بنجاح',
            'data' => $user,
            'Token' => $token
        ], 201);
    }

    // تحديث بيانات المستخدم
    public function update(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->can('تعديل مستخدم')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'تم تحديث المستخدم بنجاح',
            'data' => $user
        ], 200);
    }

    // حذف المستخدم
    public function destroy($id)
    {
        if (!Auth::user() || !Auth::user()->can('حذف مستخدم')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'تم حذف المستخدم بنجاح'
        ], 200);
    }
}
