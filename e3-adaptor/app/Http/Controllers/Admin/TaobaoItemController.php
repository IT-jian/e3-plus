<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\Sys\Shop;
use App\Models\TaobaoItem;
use App\Services\Adaptor\AdaptorTypeEnum;
use App\Services\Adaptor\Taobao\Downloader\ItemDownloader;
use App\Services\Adaptor\Taobao\Transformer\ItemTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 淘宝商品
 * Class TaobaoItemController
 * @package App\Http\Controllers\Admin
 */
class TaobaoItemController extends Controller
{
    /**
     * Display a listing of the 淘宝商品.
     * GET|HEAD /taobao_item
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

        if ($status = $request->get('num_iid')) {
            $where['num_iid'] = $status;
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

        $taobaoItems = TaobaoItem::where($where)
                    ->paginate($request->get('perPage', 15));

        return $taobaoItems;
    }

    /**
     * Store a newly created 淘宝商品 in storage.
     * POST /taobao_item
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = TaobaoItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $taobaoItem = TaobaoItem::create($input);

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($taobaoItem);
    }

    /**
     * Display the specified 淘宝商品.
     * GET|HEAD /taobao_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $taobaoItem = TaobaoItem::findOrFail($id);

        return $this->respond($taobaoItem);
    }

    /**
     * Update the specified 淘宝商品 in storage.
     * PUT/PATCH /taobao_item/{id}
     *
     * @param  int $id
     * @param Request $request
     *
     * @return mixed
     */
    public function update(Request $request,$id)
    {
        $rules = TaobaoItem::$rules;
        if ($rules) {
            $this->validate($request, $rules);
        }
        $input = $request->all();

        $taobaoItem = TaobaoItem::findOrFail($id);

        $taobaoItem->fill($input)->save();

        return $this->respond($taobaoItem);
    }

    /**
     * Remove the specified 淘宝商品 from storage.
     * DELETE /taobao_item/{id}
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $taobaoItem = TaobaoItem::findOrFail($id);

        $taobaoItem->delete();

        return $this->success([]);
    }

    public function fetch(Request $request)
    {
        $this->validate($request, ['num_iid' => 'required']);
        $numIid = $request->input('num_iid');
        if ($numIid) {
            $result = Adaptor::platform('taobao')->download(AdaptorTypeEnum::ITEM, ['num_iid' => $numIid]);
        }

        return $this->respond([]);
    }

    // 转入标准表
    public function transfer(Request $request)
    {
        $numIid = $request->input('num_iid');
        $result = Adaptor::platform('taobao')->transfer(AdaptorTypeEnum::ITEM_BATCH, ['num_iids' => [$numIid]]);
        return $this->success([$result]);
    }

}
