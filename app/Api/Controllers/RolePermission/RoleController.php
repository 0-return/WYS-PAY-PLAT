<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/11/24
 * Time: 下午3:32
 */

namespace App\Api\Controllers\RolePermission;


use App\Api\Controllers\BaseController;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleController extends BaseController
{
    //角色列表
    public function role_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
                $created_id = $public->merchant_id;
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';
                $created_id = $public->user_id;

            }


            $roles = Role::where('created_id', $created_id)
                ->where('guard_name', $guard_name)
                ->select('id as role_id', 'display_name')
                ->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($roles);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    //权限列表
    public function permission_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
                $created_id = $public->merchant_id;
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';
                $created_id = $public->user_id;

            }

            $where = [];

            //服务商以上
            if ($public->level > 0) {
                $where[] = ['name', '!=', '系统更新'];
                $where[] = ['name', '!=', 'APP配置'];
                $where[] = ['name', '!=', '推送配置'];
                $where[] = ['name', '!=', '短信配置'];
                $where[] = ['name', '!=', '门店配置'];
                $where[] = ['name', '!=', '系统配置'];

            }

            //代理商以上
            if ($public->level > 1) {
                $where[] = ['name', '!=', '支付宝应用配置'];
                $where[] = ['name', '!=', '微信应用配置'];
                $where[] = ['name', '!=', '京东金融配置'];
                $where[] = ['name', '!=', '新大陆配置'];
                $where[] = ['name', '!=', '和融通配置'];
                $where[] = ['name', '!=', '支付配置'];
                $where[] = ['name', '!=', '角色权限管理'];

            }

            $list = Permission::where('pid', 0)
                ->where($where)
                ->select('id as permission_id', 'name', 'pid', 'display_name')
                ->get();
            if ($list) {
                foreach ($list as &$per) {
                    $child = Permission::where('pid', $per['permission_id'])
                        ->select('id as permission_id', 'name', 'pid', 'display_name')
                        ->get();
                    if ($child) $per['child'] = $child;
                }
                unset($per);
            }

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($list);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    //添加角色
    public function add_role(Request $request)
    {
        try {
            $public = $this->parseToken();
            $display_name = $request->get('display_name');
            $pid = $request->get('pid', 0);
            $guard_name = '';
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
                $created_id = $public->merchant_id;
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';
                $created_id = $public->user_id;

            }


            $check_data = [
                'display_name' => '角色名字',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $role = Role::create([
                    'name' => time(),
                    'pid' => $pid,
                    'display_name' => $display_name,
                    'guard_name' => $guard_name,
                    'created_id' => $created_id,
                ]
            );


            return json_encode([
                "status" => 1,
                "message" => "添加成功"
            ]);

        } catch (\Exception $e) {
            return json_encode([
                "status" => 2,
                "message" => $e->getMessage()
            ]);
        }
    }

    //添加权限
    public function add_permission(Request $request)
    {
        try {
            $public = $this->parseToken();
            $display_name = $request->get('display_name');
            $pid = $request->get('pid', 0);

            $guard_name = '';
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';
            }

            //权限暂不可以添加
            if ($public->level > 0) {
                return json_encode([
                    "status" => 2,
                    "message" => "权限暂不可以添加"
                ]);
            }


            $check_data = [
                'display_name' => '权限名字',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $permission = Permission::create(
                [
                    'name' => $display_name,
                    'display_name' => $display_name,
                    'guard_name' => $guard_name,
                    'pid' => $pid
                ]
            );


            return json_encode([
                "status" => 1,
                "message" => "添加成功"
            ]);

        } catch (\Exception $e) {
            return json_encode([
                "status" => 2,
                "message" => $e->getMessage()
            ]);
        }
    }

    //分配角色
    public function assign_role(Request $request)
    {

        try {
            $public = $this->parseToken();
            $role_id = $request->get('role_id');
            $role_id = explode(',', $role_id);
            $user_id = $request->get('customer_id');
            $guard_name = '';
            $model_type = "";
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
                $model_type = "App\Models\Merchant";
                $user = Merchant::where('id', $user_id)->first();
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';
                $model_type = "App\Models\User";
                $user = User::where('id', $user_id)->first();

            }

            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '用户不存在'
                ]);
            }

            $check_data = [
                'customer_id' => '用户',
                'role_id' => '角色',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            DB::table('model_has_roles')
                ->where('model_id', $user_id)
                ->where('model_type', $model_type)
                ->delete();


            foreach ($role_id as $k => $v) {
                $user->assignRole($v);
            }


            return json_encode([
                "status" => 1,
                "message" => "分配成功"
            ]);

        } catch (\Exception $e) {
            return json_encode([
                "status" => 2,
                "message" => $e->getMessage()
            ]);
        }

    }

    //分配权限
    public function assign_permission(Request $request)
    {

        try {
            $public = $this->parseToken();
            $permission_id = $request->get('permission_id');
            $permission_id = explode(',', $permission_id);
            $role_id = $request->get('role_id');
            $role = Role::where('id', $role_id)->first();
            if (!$role) {
                return json_encode([
                    'status' => 2,
                    'message' => '角色不存在'
                ]);
            }


            $guard_name = '';
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';
            }


            $check_data = [
                'permission_id' => '权限',
                'role_id' => '角色',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            //先删除
            DB::table('role_has_permissions')
                ->where('role_id', $role_id)
                ->delete();

            foreach ($permission_id as $k => $v) {
                $permission = Permission::where('id', $v)
                    ->select('id')
                    ->first();
                if (!$permission) {
                    continue;
                }
                $role->givePermissionTo($v);
            }


            return json_encode([
                "status" => 1,
                "message" => "分配成功"
            ]);

        } catch (\Exception $e) {
            return json_encode([
                "status" => 2,
                "message" => $e->getMessage()
            ]);
        }

    }


    //查询 用户已分配角色

    public function user_role_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            $user_id = $request->get('customer_id');
            $guard_name = "";
            $model_type = "";
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
                $model_type = "App\Models\Merchant";
                //  $user = Merchant::where('id', $user_id)->first();
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';
                $model_type = "App\Models\User";
                // $user = User::where('id', $user_id)->first();

            }

            $roles = DB::table('model_has_roles')
                ->where('model_type', $model_type)
                ->where('model_id', $user_id)
                ->select('role_id')
                ->get();

            $this->message = '数据返回成功';
            return $this->format($roles);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    //查询 角色已分配权限

    public function role_permission_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            $role_id = $request->get('role_id');

            $roles = DB::table('role_has_permissions')
                ->where('role_id', $role_id)
                ->select('permission_id')
                ->get();

            $this->message = '数据返回成功';
            return $this->format($roles);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    public function del_role(Request $request)
    {
        try {
            $public = $this->parseToken();
            $role_id = $request->get('role_id');
            $guard_name = '';
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';

            }
            Role::where('id', $role_id)->delete();
            $this->message = '角色删除成功';
            return $this->format();


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    public function del_permission(Request $request)
    {
        try {
            $public = $this->parseToken();
            $role_id = $request->get('permission_id');
            $guard_name = '';
            if ($public->type == "merchant") {
                $guard_name = 'merchant.api';
            }

            if ($public->type == "user") {
                $guard_name = 'user.api';

            }

            //权限暂不可以添加
            if ($public->level > 0) {
                return json_encode([
                    "status" => 2,
                    "message" => "权限暂不可以删除"
                ]);
            }


            Permission::orWhere('id', $role_id)
                ->orWhere('pid', $role_id)
                ->delete();
            $this->message = '权限删除成功';
            return $this->format();


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


}