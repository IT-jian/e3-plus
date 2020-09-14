<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sys\Shop;
use App\Models\TaobaoInvoiceApply;
use App\Services\AisinoInvoiceServer;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * 淘宝开票申请
 * Class TaobaoInvoiceApplyController
 * @package App\Http\Controllers\Admin
 */
class TaobaoInvoiceApplyController extends Controller
{
    /**
     * Display a listing of the 淘宝开票申请.
     * GET|HEAD /taobao_invoice_apply
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

        if ($triggerStatus = $request->get('trigger_status')) {
            $where['trigger_status'] = $triggerStatus;
        }

        if ($business_type = $request->get('business_type')) {
            $where['business_type'] = $business_type;
        }

        if ($apply_id = $request->get('apply_id')) {
            $where['apply_id'] = $apply_id;
        }

        if ($apply_id = $request->get('apply_id')) {
            $where['apply_id'] = $apply_id;
        }

        if ($queryStatus = $request->get('query_status')) {
            $where['query_status'] = $queryStatus;
        }

        if ($pushStatus = $request->get('push_status')) {
            $where['push_status'] = $pushStatus;
        }

        if ($platformTid = $request->get('platform_tid')) {
            $where['platform_tid'] = $platformTid;
        }

        if ($uploadStatus = $request->get('upload_status')) {
            $where['upload_status'] = $uploadStatus;
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

        $taobaoInvoiceApplies = TaobaoInvoiceApply::where($where)
                    ->paginate($request->get('perPage', 15));

        return $taobaoInvoiceApplies;
    }

    /**
     * 开票查询平台
     *
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function fetch(Request $request)
    {
        $this->validate($request, ['apply_id' => 'required']);
        $applyId = $request->input('apply_id');
        if ($applyId) {
            $invoiceApply = TaobaoInvoiceApply::where('apply_id', $applyId)->firstOrFail();
            $server = new AisinoInvoiceServer();
            $detail = $server->fetchApply($invoiceApply);
            $invoiceApply->origin_content = $detail;
            $invoiceApply->query_at = Carbon::now();
            $invoiceApply->save();
        }

        return $this->respond([]);
    }

    /**
     * 订单开票详情查询 omini
     *
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function detail(Request $request)
    {
        $this->validate($request, ['apply_id' => 'required']);
        $applyId = $request->input('apply_id');
        if ($applyId) {
            $invoiceApply = TaobaoInvoiceApply::where('apply_id', $applyId)->firstOrFail();
            $server = new AisinoInvoiceServer();
            $response = $server->fetchDetail($invoiceApply);
            if ($response['status']) {
                $invoiceApply->origin_detail = $response['data'];
                $invoiceApply->save();
            } else {
                return $this->failed($response['message']);
            }
        }

        return $this->respond([]);
    }

    /**
     * 开票申请 aisino
     *
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function push(Request $request)
    {
        $this->validate($request, ['apply_id' => 'required']);
        $applyId = $request->input('apply_id');
        if ($applyId) {
            $invoiceApply = TaobaoInvoiceApply::where('apply_id', $applyId)->firstOrFail();
            $server = new AisinoInvoiceServer();
            $response = $server->invoiceCreate($invoiceApply);
            if ($response['status']) {
                return $this->respond([]);
            } else {
                return $this->failed($response['message']);
            }
        }

        return $this->respond([]);
    }

    /**
     * 上传平台
     *
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function upload(Request $request)
    {
        $this->validate($request, ['apply_id' => 'required']);
        $applyId = $request->input('apply_id');
        if ($applyId) {
            $invoiceApply = TaobaoInvoiceApply::where('apply_id', $applyId)->firstOrFail();
            $server = new AisinoInvoiceServer();
            $response = $server->updateDetailApi($invoiceApply);
            if ($response['status']) {
                $invoiceApply->origin_detail = $response;
                $invoiceApply->save();
            } else {
                return $this->failed($response['message']);
            }
        }

        return $this->respond([]);
    }
}
