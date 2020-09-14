<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysStdTradePromotion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 标准订单促销
 * Class SysStdTradePromotionController
 * @package App\Http\Controllers\Admin
 */
class SysStdTradePromotionController extends Controller
{
    /**
     * Display a listing of the 标准订单促销.
     * GET|HEAD /sys_std_trade_promotion
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($id = $request->get('id')) {
            $where['id'] = $id;
        }

        if ($promotionName = $request->get('promotion_name')) {
            $where['promotion_name'] = $promotionName;
        }

        if ($discountFee = $request->get('discount_fee')) {
            $where['discount_fee'] = $discountFee;
        }

        if ($giftItemId = $request->get('gift_item_id')) {
            $where['gift_item_id'] = $giftItemId;
        }
        $sysStdTradePromotions = SysStdTradePromotion::where($where)
                    ->paginate($request->get('perPage', 15));

        return $sysStdTradePromotions;
    }

    /**
     * Store a newly created 标准订单促销 in storage.
     * POST /sys_std_trade_promotion
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = SysStdTradePromotion::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdTradePromotion = SysStdTradePromotion::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($sysStdTradePromotion);
    }

    /**
     * Display the specified 标准订单促销.
     * GET|HEAD /sys_std_trade_promotion/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $sysStdTradePromotion = SysStdTradePromotion::findOrFail($id);

        return $this->respond($sysStdTradePromotion);
    }

    /**
     * Update the specified 标准订单促销 in storage.
     * PUT/PATCH /sys_std_trade_promotion/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = SysStdTradePromotion::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $sysStdTradePromotion = SysStdTradePromotion::findOrFail($id);

        $sysStdTradePromotion->fill($input)->save();

        return $this->respond($sysStdTradePromotion);
    }

    /**
     * Remove the specified 标准订单促销 from storage.
     * DELETE /sys_std_trade_promotion/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $sysStdTradePromotion = SysStdTradePromotion::findOrFail($id);

        $sysStdTradePromotion->delete();

        return $this->success([]);
    }

}
