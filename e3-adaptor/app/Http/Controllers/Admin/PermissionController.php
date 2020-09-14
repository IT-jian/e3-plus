<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $where = [];
        if ($name = $request->get('name')) {
            $where[] = ['desc', 'like', "%{$name}%"];
        }

        if ($desc = $request->get('desc')) {
            $where[] = ['desc', 'like', "%{$desc}%"];
        }

        if ($request->filled('parent_id')) {
            $where['parent_id'] = $request->get('parent_id');
        }

        if ($request->filled('keyword')) {
            $where[] = ['name', 'like', "%{$request->keyword}%"];
        }

        $permissions = Permission::where($where)->with([])
            ->paginate($request->get('perPage', 15));

        return $permissions;
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $permission = Permission::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($permission);
    }

    public function show($id)
    {
        $permission = Permission::with([])->findOrFail($id);

        return $this->respond($permission);
    }

    public function update(Request $request,$id)
    {
        $input = $request->all();

        $permission = Permission::findOrFail($id);

        $permission->fill($input)->save();

        return $this->respond($permission);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        $permission->delete();

        return $this->success([]);
    }

    public function tree(Request $request)
    {
        $where = [];
        if ($request->filled('keyword')) {
            $where[] = ['name', 'like', "%{$request->keyword}%"];
            $where[] = ['desc', 'like', "%{$request->keyword}%", 'or'];
        }
        $permissions = Permission::getPermissions([])->map(function ($permission) {
            return [
                'id' => $permission->id,
                'pid' => $permission->parent_id,
                'label' => $permission->desc,
            ];
        });
        return $permissions;
    }
}
