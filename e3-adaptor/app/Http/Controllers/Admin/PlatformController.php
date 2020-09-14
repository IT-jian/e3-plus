<?php

namespace App\Http\Controllers\Admin;

use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlatformController extends Controller
{
    public function index(Request $request)
    {
        $where = [];

        if ($name = $request->get('id')) {
            $where['id'] = $name;
        }
        
        if ($code = $request->get('code')) {
            $where['code'] = $code;
        }
        
        if ($ids = $request->get('ids')) {
            $where[] = ['in' => ['id' => $ids]];
        }

        if ($name = $request->get('name')) {
            $where[] = ['name', 'like', "%{$name}%"];
        }

        $operationLogs = Platform::where($where)
            ->paginate($request->get('perPage', 15));

        return $operationLogs;
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $platform = Platform::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($platform);
    }

    public function show($id)
    {
        $platform = Platform::findOrFail($id);
        
        return $this->respond($platform);
    }

    public function update(Request $request,$id)
    {
        $input = $request->all();

        $platform = Platform::findOrFail($id);

        $platform->fill($input)->save();

        return $this->respond($platform);
    }

    //
    public function destroy($id)
    {
        $platform = Platform::findOrFail($id);

        $platform->delete();

        return $this->success([]);
    }
}
