<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function createRole(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission

            if (!auth()->user()->can('Create Role')) {
                return response()->json([
                    'error' => 'Bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }
            //  Check validator
            $validator = Validator::make($request->all(), [
                'role_name' => 'bail|required|regex:/([\p{L}0-9 ]+)$/u|min:2|max:20|unique:roles,name'
            ], [
                'role_name.required' => 'Vui lòng nhập tên Role',
                'role_name.regex' => 'Tên Role phải là chữ hoặc số',
                'role_name.min' => 'Tên Role phải dài hơn 2 ký tự',
                'role_name.max' => 'Tên Role không vượt quá 20 ký tự',
                'role_name.unique' => 'Role này đã tồn tại'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Tạo mới Role
            Role::create(['name' => $request->input('role_name')]);

            return response()->json(['message' => 'Role đã tạo thành công.']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.createRole'
            ]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function updateRole(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission
            if (!auth()->user()->can('Edit Role')) {
                return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền truy cập chức năng.']);
            }
            $role = Role::findById($id);
            $newName = $request->input('role_name');
            if ($newName === $role->name) {
                $role->syncPermissions($request->input('permissions'));
                return response()->json(['status' => 'ok', 'message' => 'Cập nhật thành công']);
            } else {
                $validator = Validator::make($request->all(), [
                    'role_name' => 'bail|required|regex:/([\p{L}0-9 ]+)$/u|min:2|max:20|unique:roles,name'
                ], [
                    'role_name.required' => 'Vui lòng nhập tên Role',
                    'role_name.regex' => 'Tên Role phải là chữ hoặc số',
                    'role_name.min' => 'Tên Role phải dài hơn 2 ký tự',
                    'role_name.max' => 'Tên Role không vượt quá 20 ký tự',
                    'role_name.unique' => 'Role này đã tồn tại',
                ]);
                // Check validator
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                    ]);
                }
                // Action
                if (in_array($id, [1, 2, 3,4])) {
                    return response()->json(['status' => 'error', 'message' => 'Vai trò mặc định không thể sửa tên.']);
                } else {
                    $role->name = $newName;
                    $role->save();
                    $role->syncPermissions($request->input('permissions'));
                    return response()->json(['status' => 'ok', 'message' => 'Cập nhật thành công']);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function deleteRole(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission
            if (!auth()->user()->can('Delete Role')) {
                return response()->json([
                    'status' => 'error', 'message' => 'Bạn không có quyền truy cập chức năng này.'
                ]);
            }
            // Check validator
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|numeric|exists:roles,id',
            ], [
                'role_id.required' => 'Thiếu dữ liệu',
                'role_id.numeric' => 'Sai kiểu dữ liệu',
                'role_id.exists' => 'Dữ liệu không tồn tại'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }
            // Action
            if (in_array($request->input('role_id'), [1, 2, 3, 4])) {
                return response()->json(['status' => 'error', 'message' => 'Vai trò hệ thống không thể xoá']);
            } else {
                $role = Role::findById($request->input('role_id'));
                if (!empty($role->users()->first())) {
                    return response()->json([
                        'status' => 'error', 'message' => 'Vai trò đang có tài khoản không thể xoá'
                    ]);
                } else {

                    $role->delete();
                    return response()->json(['status' => 'ok', 'message' => 'Xoá vai trò thành công']);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => substr($e->getMessage(), 0, 150)
            ]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function createPermission(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'per_name' => 'bail|required|regex:/([\p{L}0-9 ]+)$/u|min:2|max:20|unique:permissions,name'
            ], [
                'per_name.required' => 'Vui lòng nhập tên Permission',
                'per_name.regex' => 'Tên Permission phải là chữ hoặc số',
                'per_name.min' => 'Tên Permission phải dài hơn 2 ký tự',
                'per_name.max' => 'Tên Permission không vượt quá 20 ký tự',
                'per_name.unique' => 'Permission này đã tồn tại'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Tạo mới Permission
            Permission::create(['name' => $request->input('per_name')]);

            return response()->json(['message' => 'Permission đã tạo thành công.']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.createPermission'
            ]);
        }
    }
//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function setPermission(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission

            if (!auth()->user()->can('PerToRole')) {
                return response()->json([
                    'error' => 'Bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }
            // Validator
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|numeric|exists:roles,id',
                'permission_id' => 'required|numeric|exists:permissions,id',
                'isSet' => 'required|Boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }
            $role = Role::findById($request->input('role_id'));
            $permission = Permission::findById($request->input('permission_id'));
            if ($request->input('isSet')) {
                $permission->assignRole($role);
            } else {
                $permission->removeRole($role);
            }
            return response()->json(['message' => 'Update Permission role success.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.setPermission'
            ]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function setRole(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission

            if (!auth()->user()->can('RoleToUser')) {
                return response()->json([
                    'error' => 'Bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }
            //Validator
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|numeric|exists:roles,id',
                'user_id' => 'required|numeric|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            $role = Role::findById($request->input('role_id'));
            $user = User::find($request->input('user_id'));
            $user->syncRoles($role);
            return response()->json(['message' => 'Update User role success.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.setRole'
            ]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function listRoles(): \Illuminate\Http\JsonResponse
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('id')->get();
        $formattedRoles = $roles->map(function ($role) {
            return [
                "id" => $role->id,
                "name" => $role->name,
                "permissions" => $role->permissions->pluck('name'),
                "totalUser" => $role->users->count()
            ];
        });
        return response()->json($formattedRoles);
    }
//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//

    public function listPermissions(): \Illuminate\Http\JsonResponse
    {
        $permissions = Permission::pluck('name');
        return response()->json($permissions);
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function createRoleWithPermissions(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission

            if (!auth()->user()->can('Create Role')) {
                return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền truy cập chức năng.']);
            }
            //  Check validator
            $validator = Validator::make($request->all(), [
                'role_name' => 'bail|required|regex:/([\p{L}0-9 ]+)$/u|min:2|max:20|unique:roles,name'
            ], [
                'role_name.required' => 'Vui lòng nhập tên Role',
                'role_name.regex' => 'Tên Role phải là chữ hoặc số',
                'role_name.min' => 'Tên Role phải dài hơn 2 ký tự',
                'role_name.max' => 'Tên Role không vượt quá 20 ký tự',
                'role_name.unique' => 'Role này đã tồn tại'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error', 'message' => implode(PHP_EOL, $validator->errors()->all())
                ]);
            }

            // Tạo mới Role
            $role = Role::create(['name' => $request->input('role_name')]);

            $role->syncPermissions($request->input('permissions'));
            return response()->json(['status' => 'ok', 'message' => 'Tạo Vai trò thành công']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => substr($e->getMessage(), 0, 150)]);
        }
    }

//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
//End File
}
