<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\TaobaoRefund;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Taobao\Downloader\RefundDownloader;
use App\Services\Adaptor\Taobao\Transformer\RefundTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * 淘宝退单
 * Class TaobaoRefundController
 * @package App\Http\Controllers\Admin
 */
class TaobaoRefundController extends Controller
{
    /**
     * Display a listing of the 淘宝退单.
     * GET|HEAD /taobao_refund
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

        if ($refund_id = $request->get('refund_id')) {
            $where['refund_id'] = $refund_id;
        }

        if ($tid = $request->get('tid')) {
            $where['tid'] = $tid;
        }

        if ($oid = $request->get('oid')) {
            $where['oid'] = $oid;
        }

        if ($status = $request->get('status')) {
            $where['status'] = $status;
        }

        if ($numIid = $request->get('num_iid')) {
            $where['num_iid'] = $numIid;
        }

        if ($created = $request->get('created') && isset($created['1'])) {
            $where[] = ['created', '>=', $created[0]];
            $where[] = ['created', '<=', $created[1]];
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

        $taobaoRefunds = TaobaoRefund::where($where)
                    ->paginate($request->get('perPage', 15));

        return $taobaoRefunds;
    }

    /**
     * Store a newly created 淘宝退单 in storage.
     * POST /taobao_refund
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = TaobaoRefund::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $taobaoRefund = TaobaoRefund::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($taobaoRefund);
    }

    /**
     * Display the specified 淘宝退单.
     * GET|HEAD /taobao_refund/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $taobaoRefund = TaobaoRefund::findOrFail($id);

        return $this->respond($taobaoRefund);
    }

    /**
     * Update the specified 淘宝退单 in storage.
     * PUT/PATCH /taobao_refund/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = TaobaoRefund::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $taobaoRefund = TaobaoRefund::findOrFail($id);

        $taobaoRefund->fill($input)->save();

        return $this->respond($taobaoRefund);
    }

    /**
     * Remove the specified 淘宝退单 from storage.
     * DELETE /taobao_refund/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $taobaoRefund = TaobaoRefund::findOrFail($id);

        $taobaoRefund->delete();

        return $this->success([]);
    }

    public function fetch(Request $request)
    {
        // $this->validate($request, ['shop_code' => 'required']);
        $refundId = $request->input('refund_id');
        if ($refundId) {
            if (Str::contains($refundId, [','])) {
                $params = [
                    'refund_ids' => explode(',', $refundId)
                ];
            } else {
                $params = [
                    'refund_ids' => [$refundId]
                ];
            }
            Adaptor::platform('taobao')->download(AdaptorTypeEnum::REFUND, $params);
            Adaptor::platform('taobao')->transfer(AdaptorTypeEnum::REFUND_BATCH, $params);
        }

        return $this->respond([]);
    }

    // 转为退单
    public function transfer(Request $request)
    {
        $refundId = $request->input('refund_id');
        $taobaoRefund = TaobaoRefund::findOrFail($refundId);
        $result = Adaptor::platform('taobao')->transfer(AdaptorTypeEnum::REFUND_BATCH, ['refund_ids' => [$refundId]]);

        return $this->success([$result]);
    }

}
