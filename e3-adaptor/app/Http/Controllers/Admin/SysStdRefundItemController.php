<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdRefundItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准退单明细
 * Class SysStdRefundItemController
 * @package App\Http\Controllers\Admin
 */
class SysStdRefundItemController extends Controller
{
    /**
     * Display a listing of the 标准退单明细.
     * GET|HEAD /sys_std_refund_item
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
        $sysStdRefundItems = SysStdRefundItem::where($where)
                    ->paginate($request->get('perPage', 15));

        return $sysStdRefundItems;
    }

    /**
     * Store a newly created 标准退单明细 in storage.
     * POST /sys_std_refund_item
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdRefundItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdRefundItem = SysStdRefundItem::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdRefundItem);
    }

    /**
     * Display the specified 标准退单明细.
     * GET|HEAD /sys_std_refund_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdRefundItem = SysStdRefundItem::findOrFail($id);

        return $this->respond($sysStdRefundItem);
    }

    /**
     * Update the specified 标准退单明细 in storage.
     * PUT/PATCH /sys_std_refund_item/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdRefundItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdRefundItem = SysStdRefundItem::findOrFail($id);

        $sysStdRefundItem->fill($input)->save();

        return $this->respond($sysStdRefundItem);
    }

    /**
     * Remove the specified 标准退单明细 from storage.
     * DELETE /sys_std_refund_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdRefundItem = SysStdRefundItem::findOrFail($id);

        $sysStdRefundItem->delete();

        return $this->success([]);
    }

}
