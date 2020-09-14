<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function profile()
    {
        $user = $this->user()->toArray();
        $user['roles'] = $this->user()->roles()->pluck('name');
        $user['permissions'] = $this->user()->getAllPermissions()->pluck('name');
        $user['avatar'] = 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1578285171580&di=8f88af29cf7632e25d3115478f973c90&imgtype=jpg&src=http%3A%2F%2Fimg1.imgtn.bdimg.com%2Fit%2Fu%3D4119382011%2C3431314157%26fm%3D214%26gp%3D0.jpg';

        return $this->respond($user);
    }

    public function index(Request $request)
    {
        $where = [];
        if ($id = $request->get('id')) {
            $where['id'] = $id;
        }
        if ($name = $request->get('name')) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $users = User::where($where);
        if ($role = $request->filled('role')) {
            $users = $users->role($request->role);
        }
        $users->with(['roles']);
        $users = $users->paginate($request->get('perPage', 15));
        return $this->respond($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return $this->respond($user);
    }

    public function store(Request $request)
    {
        $user = $request->only(['name', 'email', 'password']);
        $user['password'] = Hash::make($user['password']);
        $user = User::create($user);

        $roleIds = $request->role_ids;
        $user->syncRoles($roleIds);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($user);
    }

    public function update(Request $request,$id)
    {
        $input = $request->only(['name', 'email', 'password']);
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }
        $user = User::findOrFail($id);
        $user->fill($input)->save();

        $roleIds = $request->role_ids;
        $user->syncRoles($roleIds);

        return $this->respond($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $user->syncRoles([]);
        return $this->success([]);
    }
}
