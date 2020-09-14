<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdRefund;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准退单
 * Class SysStdRefundController
 * @package App\Http\Controllers\Admin
 */
class SysStdRefundController extends Controller
{
    /**
     * Display a listing of the 标准退单.
     * GET|HEAD /sys_std_refund
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($refundId = $request->get('refund_id')) {
            $where['refund_id'] = $refundId;
        }

        if ($tid = $request->get('tid')) {
            $where['tid'] = $tid;
        }

        if ($oid = $request->get('oid')) {
            $where['oid'] = $oid;
        }

        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        if ('true' == $request->get('has_good_return')) {
            $where['has_good_return'] = 1;
        }

        $created = $request->get('created');
        if (isset($created[1])) {
            $where[] = ['created', '>=', $created[0]];
            $where[] = ['created', '<=', $created[1]];
        }

        $sysStdRefunds = SysStdRefund::where($where)->with('items')->withCount('trade')
                    ->paginate($request->get('perPage', 15));

        return $sysStdRefunds;
    }

    /**
     * Store a newly created 标准退单 in storage.
     * POST /sys_std_refund
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdRefund::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdRefund = SysStdRefund::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdRefund);
    }

    /**
     * Display the specified 标准退单.
     * GET|HEAD /sys_std_refund/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdRefund = SysStdRefund::findOrFail($id);

        return $this->respond($sysStdRefund);
    }

    /**
     * Update the specified 标准退单 in storage.
     * PUT/PATCH /sys_std_refund/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdRefund::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdRefund = SysStdRefund::findOrFail($id);

        $sysStdRefund->fill($input)->save();

        return $this->respond($sysStdRefund);
    }

    /**
     * Remove the specified 标准退单 from storage.
     * DELETE /sys_std_refund/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdRefund = SysStdRefund::findOrFail($id);

        $sysStdRefund->delete();

        return $this->success([]);
    }

}
