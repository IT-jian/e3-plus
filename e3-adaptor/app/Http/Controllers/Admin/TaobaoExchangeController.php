<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Models\TaobaoExchange;
use App\Services\Adaptor\AdaptorTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * 淘宝换货单
 * Class TaobaoExchangeController
 * @package App\Http\Controllers\Admin
 */
class TaobaoExchangeController extends Controller
{
    /**
     * Display a listing of the 淘宝换货单.
     * GET|HEAD /taobao_exchange
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($sellerNick = $request->get('seller_nick')) {
            $where['seller_nick'] = $sellerNick;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['seller_nick'] = $shop['seller_nick'] ?? '';
        }

        if ($bizOrderId = $request->get('dispute_id')) {
            $where['dispute_id'] = $bizOrderId;
        }

        if ($bizOrderId = $request->get('biz_order_id')) {
            $where['biz_order_id'] = $bizOrderId;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        $originCreated = $request->get('origin_created');
        if (isset($originCreated[1])) {
            $where[] = ['origin_created', '>=', $originCreated[0]];
            $where[] = ['origin_created', '<=', $originCreated[1]];
        }

        $originUpdated = $request->get('origin_updated');
        if (isset($originUpdated[1])) {
            $where[] = ['origin_updated', '>=', $originUpdated[0]];
            $where[] = ['origin_updated', '<=', $originUpdated[1]];
        }

        $taobaoExchanges = TaobaoExchange::where($where)
                    ->paginate($request->get('perPage', 15));

        return $taobaoExchanges;
    }

    /**
     * Store a newly created 淘宝换货单 in storage.
     * POST /taobao_exchange
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = TaobaoExchange::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $taobaoExchange = TaobaoExchange::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($taobaoExchange);
    }

    /**
     * Display the specified 淘宝换货单.
     * GET|HEAD /taobao_exchange/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $taobaoExchange = TaobaoExchange::findOrFail($id);

        return $this->respond($taobaoExchange);
    }

    /**
     * Update the specified 淘宝换货单 in storage.
     * PUT/PATCH /taobao_exchange/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = TaobaoExchange::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $taobaoExchange = TaobaoExchange::findOrFail($id);

        $taobaoExchange->fill($input)->save();

        return $this->respond($taobaoExchange);
    }

    /**
     * Remove the specified 淘宝换货单 from storage.
     * DELETE /taobao_exchange/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $taobaoExchange = TaobaoExchange::findOrFail($id);

        $taobaoExchange->delete();

        return $this->success([]);
    }

    public function fetch(Request $request)
    {
        if ($sellerNick = $request->input('seller_nick')) {
            $shop = Shop::getShopByNick($sellerNick);
        } else {
            $this->validate($request, ['shop_code' => 'required', 'dispute_id' => 'required']);
            $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        }
        $disputeId = $request->input('dispute_id');
        if ($disputeId) {
            if (Str::contains($disputeId, [','])) {
                $disputeIds = explode(',', $disputeId);
            } else {
                $disputeIds = [$disputeId];
            }
            $params = ['dispute_ids' => $disputeIds, 'shop_code' => $shop['code']];
            Adaptor::platform('taobao')->download(AdaptorTypeEnum::EXCHANGE, $params);
        }

        return $this->respond([]);
    }

    // 转为订单
    public function transfer(Request $request)
    {
        $disputeId = $request->input('dispute_id');
        $taobaoExchange = TaobaoExchange::findOrFail($disputeId);
        $shop = Shop::select('code')->where('seller_nick', $taobaoExchange['seller_nick'])->firstOrFail();
        $shopCode = $shop['code'];
        $result = Adaptor::platform('taobao')->transfer(AdaptorTypeEnum::EXCHANGE_BATCH, ['dispute_ids' => [$disputeId], 'shop_code' => $shopCode]);

        return $this->success([$result]);
    }
}
