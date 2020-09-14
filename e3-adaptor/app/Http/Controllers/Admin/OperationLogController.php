<?php

namespace App\Http\Controllers\Admin;

use App\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OperationLogController extends Controller
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

        $operationLogs = OperationLog::where($where)
            ->paginate($request->get('perPage', 15));

        return $operationLogs;
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $operationLog = OperationLog::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($operationLog);
    }

    public function show($id)
    {
        $operationLog = OperationLog::findOrFail($id);
        
        return $this->respond($operationLog);
    }

    public function update(Request $request,$id)
    {
        $input = $request->all();

        $operationLog = OperationLog::findOrFail($id);

        $operationLog->fill($input)->save();

        return $this->respond($operationLog);
    }

    //
    public function destroy($id)
    {
        $operationLog = OperationLog::findOrFail($id);

        $operationLog->delete();

        return $this->success([]);
    }
}
