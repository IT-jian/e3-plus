<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    // 下拉选项
    public function roleSelectOptions(Request $request)
    {
        $where = [];
        if ($keyword = $request->filled('keyword')) {
            $where = ['desc', 'like', "%{$keyword}%"];
        }

        $options = Role::where($where)->get()->map(function ($role) {
            return [
                'label' => $role->desc,
                'value' => $role->id,
            ];
        });
        return $options;
    }

    public function rolePermissionIds($id)
    {
        $role = Role::find($id);
        $permissions = $role->permissions->pluck('id');
        return $permissions;
    }
}
