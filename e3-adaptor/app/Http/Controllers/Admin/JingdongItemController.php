<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Adaptor;
use App\Models\JingdongItem;
use App\Models\Sys\Shop;
use App\Services\Adaptor\AdaptorTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 京东商品
 * Class JingdongItemController
 * @package App\Http\Controllers\Admin
 */
class JingdongItemController extends Controller
{
    /**
     * Display a listing of the 京东商品.
     * GET|HEAD /jingdong_item
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $where = [];
        if ($ware_id = $request->get('ware_id')) {
            $where['ware_id'] = $ware_id;
        }

        if ($venderId = $request->get('vender_id')) {
            $where['vender_id'] = $venderId;
        }

        if ($shopCode = $request->get('shop')) {
            $shop = Shop::where('code', $shopCode)->first();
            $where['seller_nick'] = $shop['seller_nick'] ?? '';
        }

        if ($wareStatus = $request->get('ware_status')) {
            $where['ware_status'] = $wareStatus;
        }

        if ($originCreated = $request->get('origin_created') && isset($originCreated[1])) {
            $where[] = ['origin_created', '>=', $originCreated[0]];
            $where[] = ['origin_created', '<=', $originCreated[1]];
        }
        $jingdongItems = JingdongItem::where($where)
                    ->paginate($request->get('perPage', 15));

        return $jingdongItems;
    }



    public function fetch(Request $request)
    {
        if ($venderId = $request->input('vender_id')) {
            $shop = Shop::getShopByNick($venderId);
        } else {
            $this->validate($request, ['shop_code' => 'required']);
            $shop = Shop::where('code', $request->input('shop_code'))->firstOrFail();
        }
        $this->validate($request, ['ware_id' => 'required']);
        $wareId = $request->input('ware_id');
        if ($wareId) {
            Adaptor::platform('jingdong')->download(AdaptorTypeEnum::ITEM, ['ware_id' => $wareId, 'shop_code' => $shop['code']]);
        }

        return $this->respond([]);
    }

    // 转入标准表
    public function transfer(Request $request)
    {
        $wareId = $request->input('ware_id');
        $item = JingdongItem::where('ware_id', $wareId)->first();
        $shop = Shop::getShopByNick($item['vender_id']);
        $result = Adaptor::platform('jingdong')->transfer(AdaptorTypeEnum::ITEM_BATCH, ['ware_ids' => [$wareId], 'shop_code' => $shop['code']]);
        return $this->success([$result]);
    }

}
