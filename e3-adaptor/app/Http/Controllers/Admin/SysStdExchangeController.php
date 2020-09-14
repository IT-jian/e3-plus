<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdExchange;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准换货单
 * Class SysStdExchangeController
 * @package App\Http\Controllers\Admin
 */
class SysStdExchangeController extends Controller
{
    /**
     * Display a listing of the 标准换货单.
     * GET|HEAD /sys_std_exchange
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($disputeId = $request->get('dispute_id')) {
            $where['dispute_id'] = $disputeId;
        }

        if ($platform = $request->get('platform')) {
            $where['platform'] = $platform;
        }

        if ($tid = $request->get('tid')) {
            $where['tid'] = $tid;
        }

        if ($shopCode = $request->get('shop_code')) {
            $where['shop_code'] = $shopCode;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        if ($buyerLogisticNo = $request->get('buyer_logistic_no')) {
            $where['buyer_logistic_no'] = $buyerLogisticNo;
        }

        if ($sellerLogisticNo = $request->get('seller_logistic_no')) {
            $where['seller_logistic_no'] = $sellerLogisticNo;
        }
        $created = $request->get('created');
        if (isset($created[1])) {
            $where[] = ['created', '>=', $created[0]];
            $where[] = ['created', '<=', $created[1]];
        }
        $sysStdExchanges = SysStdExchange::where($where)->with('items')->withCount('trade')
                    ->paginate($request->get('perPage', 15));

        return $sysStdExchanges;
    }

    /**
     * Store a newly created 标准换货单 in storage.
     * POST /sys_std_exchange
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdExchange::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdExchange = SysStdExchange::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdExchange);
    }

    /**
     * Display the specified 标准换货单.
     * GET|HEAD /sys_std_exchange/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);

        return $this->respond($sysStdExchange);
    }

    /**
     * Update the specified 标准换货单 in storage.
     * PUT/PATCH /sys_std_exchange/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdExchange::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdExchange = SysStdExchange::findOrFail($id);

        $sysStdExchange->fill($input)->save();

        return $this->respond($sysStdExchange);
    }

    /**
     * Remove the specified 标准换货单 from storage.
     * DELETE /sys_std_exchange/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdExchange = SysStdExchange::findOrFail($id);

        $sysStdExchange->delete();

        return $this->success([]);
    }

}
