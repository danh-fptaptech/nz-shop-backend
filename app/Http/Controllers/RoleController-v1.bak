<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController_bak extends Controller
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
                return response()->json([
                    'error' => 'Bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }
            $role = Role::findById($id);
            $newName = $request->input('role_name');
            if ($newName === $role->name) {
                return response()->json(['message' => 'Tên mới và tên cũ giống nhau, không có thay đổi.']);
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
                        'error' => 'Dữ liệu không hợp lệ',
                        'errors' => $validator->errors()
                    ], 400);
                }
                // Action
                $role->name = $newName;
                $role->save();
                return response()->json(['message' => 'Update Role thành công.']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.updateRole'
            ]);
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
                    'error' => 'Bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|numeric|exists:roles,id',
            ], [
                'role_id.required' => 'Thiếu dữ liệu',
                'role_id.numeric' => 'Sai kiểu dữ liệu',
                'role_id.exists' => 'Dữ liệu không tồn tại'
            ]);
            // Check validator
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }
            // Action
            $role = Role::findById($request->input('role_id'));
            $role->delete();
            return response()->json(['message' => 'Delete Role thành công.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.deleteRole'
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
    public function assignRole(Request $request): \Illuminate\Http\JsonResponse
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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }
            $role = Role::findById($request->input('role_id'));
            $permission = Permission::findById($request->input('permission_id'));
            $permission->assignRole($role);
            return response()->json(['message' => 'Assign Role thành công.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.assignRole'
            ]);
        }
    }
//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
    public function removeRole(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check permission

            if (!auth()->user()->can('PerToRole')) {
                return response()->json([
                    'error' => 'Bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }
            //Validator
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|numeric|exists:roles,id',
                'permission_id' => 'required|numeric|exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            $role = Role::findById($request->input('role_id'));
            $permission = Permission::findById($request->input('permission_id'));
            $permission->removeRole($role);
            return response()->json(['message' => 'Remove Role thành công.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.removeRole'
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
                'user_id' => 'required|numeric|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            $role = Role::findById($request->input('role_id'));
            $user = User::find($request->input('user_id'));
            $user->assignRole($role);
            return response()->json(['message' => 'Set Role thành công.']);
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
    public function unsetRole(Request $request): \Illuminate\Http\JsonResponse
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
                'user_id' => 'required|numeric|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 400);
            }

            $role = Role::findById($request->input('role_id'));
            $user = User::find($request->input('user_id'));
            $user->removeRole($role);
            return response()->json(['message' => 'UnSet Role thành công.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - RoleController.unsetRole'
            ]);
        }
    }


//End File
}
