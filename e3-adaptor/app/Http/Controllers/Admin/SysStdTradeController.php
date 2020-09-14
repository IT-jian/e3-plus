<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdTrade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准订单
 * Class SysStdTradeController
 * @package App\Http\Controllers\Admin
 */
class SysStdTradeController extends Controller
{
    /**
     * Display a listing of the 标准订单.
     * GET|HEAD /sys_std_trade
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

        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($payStatus = $request->get('pay_status')) {
            $where['pay_status'] = $payStatus;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        $created = $request->get('created');
        if (isset($created[1])) {
            $where[] = ['created', '>=', $created[0]];
            $where[] = ['created', '<=', $created[1]];
        }

        $sysStdTrades = SysStdTrade::where($where)->with('items', 'promotions')->withCount('onlyRefund')->orderBy('created', 'desc')
                    ->paginate($request->get('perPage', 15));

        return $sysStdTrades;
    }

    /**
     * Store a newly created 标准订单 in storage.
     * POST /sys_std_trade
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdTrade::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdTrade = SysStdTrade::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdTrade);
    }

    /**
     * Display the specified 标准订单.
     * GET|HEAD /sys_std_trade/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdTrade = SysStdTrade::findOrFail($id);

        return $this->respond($sysStdTrade);
    }

    /**
     * Update the specified 标准订单 in storage.
     * PUT/PATCH /sys_std_trade/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdTrade::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdTrade = SysStdTrade::findOrFail($id);

        $sysStdTrade->fill($input)->save();

        return $this->respond($sysStdTrade);
    }

    /**
     * Remove the specified 标准订单 from storage.
     * DELETE /sys_std_trade/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdTrade = SysStdTrade::findOrFail($id);

        $sysStdTrade->delete();

        return $this->success([]);
    }
}
