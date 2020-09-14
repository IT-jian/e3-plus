<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $where = [];

        if ($name = $request->get('id')) {
            $where['id'] = $name;
        }

        if ($ids = $request->get('ids')) {
            $where[] = ['in' => ['id' => $ids]];
        }

        if ($name = $request->get('name')) {
            $where[] = ['name', 'like', "%{$name}%"];
        }

        if ($desc = $request->get('desc')) {
            $where[] = ['desc', 'like', "%{$desc}%"];
        }

        $roles = Role::where($where)->with(['permissions'])
            ->paginate($request->get('perPage', 15));

        return $roles;
    }

    public function store(Request $request)
    {
        $input = $request->except(['permissions']);

        $role = Role::create($input);

        if ($request->filled('permissions')) {
            $permissions = $request->permissions;
            $role->syncPermissions($permissions);
        }

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($role);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);

        return $this->respond($role);
    }

    public function update(Request $request,$id)
    {
        $input = $request->except('permissions');

        $role = Role::findOrFail($id);

        $role->fill($input)->save();
        if ($request->filled('permissions')) {
            $permissions = $request->permissions;
            $role->syncPermissions($permissions);
        }

        return $this->respond($role);
    }

    //
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->delete();
        $role->syncPermissions([]);

        return $this->success([]);
    }
}
