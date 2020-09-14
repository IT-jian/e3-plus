<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongComment;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Jingdong\Api\VenderComments;
use App\Services\JingdongCommentCsvExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 京东商品评论
 * Class JingdongCommentController
 * @package App\Http\Controllers\Admin
 */
class JingdongCommentController extends Controller
{
    /**
     * Display a listing of the 京东商品评论.
     * GET|HEAD /jingdong_comment
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($commentId = $request->get('comment_id')) {
            $where['comment_id'] = $commentId;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['vender_id'] = $shop['seller_nick'] ?? 0;
        }

        if ($venderId = $request->get('vender_id')) {
            $where['vender_id'] = $venderId;
        }

        if ($orderId = $request->get('order_id')) {
            $where['order_id'] = $orderId;
        }

        if ($skuId = $request->get('sku_id')) {
            $where['sku_id'] = $skuId;
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

        $startAt = $request->get('start_at');
        if (isset($startAt[1])) {
            $where[] = ['start_at', '>=', $startAt[0]];
            $where[] = ['start_at', '<=', $startAt[1]];
        }
        $jingdongComments = JingdongComment::where($where)
                    ->paginate($request->get('perPage', 15));

        return $jingdongComments;
    }

    /**
     * Store a newly created 京东商品评论 in storage.
     * POST /jingdong_comment
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = JingdongComment::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $jingdongComment = JingdongComment::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($jingdongComment);
    }

    /**
     * Display the specified 京东商品评论.
     * GET|HEAD /jingdong_comment/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $jingdongComment = JingdongComment::findOrFail($id);

        return $this->respond($jingdongComment);
    }

    /**
     * Update the specified 京东商品评论 in storage.
     * PUT/PATCH /jingdong_comment/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = JingdongComment::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $jingdongComment = JingdongComment::findOrFail($id);

        $jingdongComment->fill($input)->save();

        return $this->respond($jingdongComment);
    }

    /**
     * Remove the specified 京东商品评论 from storage.
     * DELETE /jingdong_comment/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $jingdongComment = JingdongComment::findOrFail($id);

        $jingdongComment->delete();

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
        $orderId = $request->input('order_id');
        if ($orderId) {
            $commentsApi = new VenderComments($shop);
            $comments = $commentsApi->findByOrderId($orderId);
            if (empty($comments)) {
                return $this->failed('comments not found');
            }
            foreach ($comments as $key => $comment) {
                $comments[$key]['vender_id'] = $shop['seller_nick'];
            }
            Adaptor::platform('jingdong')->download(AdaptorTypeEnum::COMMENTS, $comments);
        }

        return $this->respond([]);
    }

    public function export(Request $request)
    {
        $this->validate($request, ['export_date' => 'required']);

        $server = new JingdongCommentCsvExport();
        $date = $request->input('export_date');
        $server->exportByDate($date);

        return $this->respond([]);
    }
}
