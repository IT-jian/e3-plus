<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdExchangeItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准换货明细
 * Class SysStdExchangeItemController
 * @package App\Http\Controllers\Admin
 */
class SysStdExchangeItemController extends Controller
{
    /**
     * Display a listing of the 标准换货明细.
     * GET|HEAD /sys_std_exchange_item
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
        $sysStdExchangeItems = SysStdExchangeItem::where($where)
                    ->paginate($request->get('perPage', 15));

        return $sysStdExchangeItems;
    }

    /**
     * Store a newly created 标准换货明细 in storage.
     * POST /sys_std_exchange_item
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdExchangeItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdExchangeItem = SysStdExchangeItem::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdExchangeItem);
    }

    /**
     * Display the specified 标准换货明细.
     * GET|HEAD /sys_std_exchange_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdExchangeItem = SysStdExchangeItem::findOrFail($id);

        return $this->respond($sysStdExchangeItem);
    }

    /**
     * Update the specified 标准换货明细 in storage.
     * PUT/PATCH /sys_std_exchange_item/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdExchangeItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdExchangeItem = SysStdExchangeItem::findOrFail($id);

        $sysStdExchangeItem->fill($input)->save();

        return $this->respond($sysStdExchangeItem);
    }

    /**
     * Remove the specified 标准换货明细 from storage.
     * DELETE /sys_std_exchange_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdExchangeItem = SysStdExchangeItem::findOrFail($id);

        $sysStdExchangeItem->delete();

        return $this->success([]);
    }

}
