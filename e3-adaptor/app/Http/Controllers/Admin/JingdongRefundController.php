<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongRefund;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\Refund;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 京东退单
 * Class JingdongRefundController
 * @package App\Http\Controllers\Admin
 */
class JingdongRefundController extends Controller
{
    /**
     * Display a listing of the 京东退单.
     * GET|HEAD /jingdong_refund
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($venderId = $request->get('vender_id')) {
            $where['vender_id'] = $venderId;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['vender_id'] = $shop['seller_nick'] ?? 0;
        }

        if ($service_id = $request->get('service_id')) {
            $where['service_id'] = $service_id;
        }

        if ($orderId = $request->get('order_id')) {
            $where['order_id'] = $orderId;
        }

        if ($serviceStatus = $request->get('service_status')) {
            $where['service_status'] = $serviceStatus;
        }

        if ($customerExpect = $request->get('customer_expect')) {
            $where['customer_expect'] = $customerExpect;
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

        $jingdongRefunds = JingdongRefund::where($where)
                    ->paginate($request->get('perPage', 15));

        return $jingdongRefunds;
    }

    /**
     * Store a newly created 京东退单 in storage.
     * POST /jingdong_refund
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = JingdongRefund::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $jingdongRefund = JingdongRefund::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($jingdongRefund);
    }

    /**
     * Display the specified 京东退单.
     * GET|HEAD /jingdong_refund/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $jingdongRefund = JingdongRefund::findOrFail($id);

        return $this->respond($jingdongRefund);
    }

    /**
     * Update the specified 京东退单 in storage.
     * PUT/PATCH /jingdong_refund/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = JingdongRefund::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $jingdongRefund = JingdongRefund::findOrFail($id);

        $jingdongRefund->fill($input)->save();

        return $this->respond($jingdongRefund);
    }

    /**
     * Remove the specified 京东退单 from storage.
     * DELETE /jingdong_refund/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $jingdongRefund = JingdongRefund::findOrFail($id);

        $jingdongRefund->delete();

        return $this->success([]);
    }

    public function fetch(Request $request)
    {
        if ($venderId = $request->input('vender_id')) {
            $shop = Shop::getShopByNick($venderId);
        } else {
            $this->validate($request, ['shop_code' => 'required']);
            $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        }
        $serviceId = $request->input('service_id');
        $order_id = $request->input('order_id');
        if ($serviceId || $order_id) {
            $refundApi = new Refund($shop);
            $refunds = $refundApi->find($serviceId, $order_id);
            foreach ($refunds as $refund) {
                $refund['shop'] = $shop;
                Adaptor::platform('jingdong')->download(AdaptorTypeEnum::REFUND, $refund);
            }
        }

        return $this->respond([]);
    }

    // 转为退单
    public function transfer(Request $request)
    {
        $serviceId = $request->input('service_id');
        $refund = JingdongRefund::findOrFail($serviceId);
        if (20 == $refund['customer_expect']) {
            $result = Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::EXCHANGE, ['service_id' => $serviceId]);
        } else {
            $result = Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::REFUND, ['service_id' => $serviceId]);
        }

        return $this->success([$result]);
    }}
