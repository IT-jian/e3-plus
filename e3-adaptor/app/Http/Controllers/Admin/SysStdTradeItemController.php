<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdTradeItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准订单明细
 * Class SysStdTradeItemController
 * @package App\Http\Controllers\Admin
 */
class SysStdTradeItemController extends Controller
{
    /**
     * Display a listing of the 标准订单明细.
     * GET|HEAD /sys_std_trade_item
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($tid = $request->get('tid')) {
            $where['tid'] = $tid;
        }

        if ($oid = $request->get('oid')) {
            $where['oid'] = $oid;
        }

        if ($numIid = $request->get('num_iid')) {
            $where['num_iid'] = $numIid;
        }

        if ($outerIid = $request->get('outer_iid')) {
            $where['outer_iid'] = $outerIid;
        }

        if ($outerSkuId = $request->get('outer_sku_id')) {
            $where['outer_sku_id'] = $outerSkuId;
        }
        $sysStdTradeItems = SysStdTradeItem::where($where)
                    ->paginate($request->get('perPage', 15));

        return $sysStdTradeItems;
    }

    /**
     * Store a newly created 标准订单明细 in storage.
     * POST /sys_std_trade_item
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdTradeItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdTradeItem = SysStdTradeItem::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdTradeItem);
    }

    /**
     * Display the specified 标准订单明细.
     * GET|HEAD /sys_std_trade_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdTradeItem = SysStdTradeItem::findOrFail($id);

        return $this->respond($sysStdTradeItem);
    }

    /**
     * Update the specified 标准订单明细 in storage.
     * PUT/PATCH /sys_std_trade_item/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdTradeItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdTradeItem = SysStdTradeItem::findOrFail($id);

        $sysStdTradeItem->fill($input)->save();

        return $this->respond($sysStdTradeItem);
    }

    /**
     * Remove the specified 标准订单明细 from storage.
     * DELETE /sys_std_trade_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdTradeItem = SysStdTradeItem::findOrFail($id);

        $sysStdTradeItem->delete();

        return $this->success([]);
    }

}
