<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Models\TaobaoComment;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Taobao\Api\TradeRates;
use App\Services\TaobaoCommentCsvExport;
use Illuminate\Http\Request;

/**
 * 淘宝订单评论
 * Class TaobaoCommentController
 * @package App\Http\Controllers\Admin
 */
class TaobaoCommentController extends Controller
{
    /**
     * Display a listing of the 淘宝订单评论.
     * GET|HEAD /taobao_comment
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

        if ($oid = $request->get('oid')) {
            $where['oid'] = $oid;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['seller_nick'] = $shop['seller_nick'] ?? '';
        }

        if ($numIid = $request->get('num_iid')) {
            $where['num_iid'] = $numIid;
        }

        if ($created = $request->get('created') && isset($created[1])) {
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

        $taobaoComments = TaobaoComment::where($where)
                    ->paginate($request->get('perPage', 15));

        return $taobaoComments;
    }

    public function export(Request $request)
    {
        $this->validate($request, ['export_date' => 'required']);

        $server = new TaobaoCommentCsvExport();
        $date = $request->input('export_date');
        $server->exportByDate($date);

        return $this->respond([]);
    }

    public function fetch(Request $request)
    {
        if ($sellerNick = $request->input('seller_nick')) {
            $shop = Shop::getShopByNick($sellerNick);
        } else {
            $this->validate($request, ['shop_code' => 'required', 'tid' => 'required']);
            $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        }
        $tid = $request->input('tid');
        if ($tid) {
            $commentsApi = new TradeRates($shop);
            $comments = $commentsApi->findByTid($tid);
            if (empty($comments)) {
                return $this->failed('comment api response empty');
            }
            Adaptor::platform('taobao')->download(AdaptorTypeEnum::COMMENTS, $comments);
        }

        return $this->respond([]);
    }
}
