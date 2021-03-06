<?php


namespace App\Services\Hub\Qimen\Adidas\Request;


use App\Models\SysStdExchange;
use App\Models\SysStdTrade;
use App\Services\Hub\Adidas\Request\Transformer\ExchangeCreateTransformer;
use App\Services\Hub\Qimen\BaseRequest;
use App\Services\Platform\Taobao\Qimen\Top\Request\TaobaoPosWeborderSyncRequest;
use Illuminate\Contracts\Support\Arrayable;

/**
 * 换货单加强版报文组织 -- 走奇门
 *
 * Class ExchangeCreateRequest
 * @package App\Services\Hub\Adidas\Request
 */
class ExchangeCreateRequest extends BaseRequest
{
    protected $apiName = 'qimen.taobao.pos.weborder.sync';

    public $keyword = '';
    public $shop;

    public function getApiMethodName()
    {
        return $this->apiName;
    }

    public function setContent($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $stdExchange = $id;
        } else {
            $stdExchange = SysStdExchange::where('dispute_id', $id)->first();
        }
        $stdTrade = SysStdTrade::where('tid', $stdExchange['tid'])->first();
        // 设置时间戳
        $this->setDataVersion(strtotime($stdTrade['modified']));
        $this->keyword = $stdExchange['dispute_id'] ?? '';
        // 设置店铺
        $this->setShop($stdTrade['shop_code']);
        // 设置拓展属性
        $req = new TaobaoPosWeborderSyncRequest();
        $extendProps = $this->getExtendProps($stdTrade, $stdExchange);
        $extendProps = json_encode($extendProps);

        $req->setOrderBillCode($stdTrade['tid']);
        $req->setOrderWebCod($stdTrade['tid']);
        $req->setShopCode($stdTrade['shop_code']);
        $req->setBillTime($stdTrade['created']);
        $req->setQuantity("1");
        $req->setMoney($stdTrade['total_fee']);
        $req->setRealMoney($stdTrade['payment']);
        $req->setDiscount($stdTrade['discount_fee']);
        $req->setPaymethod("1");
        $req->setName($stdTrade['buyer_nick']);
        $req->setPhone($stdTrade['receiver_mobile']);
        $req->setProvince($stdTrade['receiver_country']);
        $req->setCity($stdTrade['receiver_city']);
        $req->setDistrict($stdTrade['receiver_district']);
        $req->setAddres($stdTrade['receiver_address']);
        $req->setShippingAddress($stdTrade['receiver_address']);
        $req->setThAct("退单");
        $req->setSystem("3"); // 来源系统 0-POS、1-中台、2-Retail、3-OMS、4-其他
        $req->setExtendProps($extendProps);
        $req->setLypt("1"); // 来源平台：0-后台1-淘宝2-拍拍3-OS主站4-分销商5-京东11-亚马逊13-一号店等'
        $req->setLyzdDm($stdTrade['shop_code']);
        $req->setLyzdMc($stdTrade['shop_code']);
        $req->setLyorgDm($stdTrade['shop_code']);
        $req->setLyorgMc($stdTrade['shop_code']);
        $req->setXdzdDm($stdTrade['shop_code']);
        $req->setGkly(empty($stdTrade['buyer_message']) ? '暂无' : $stdTrade['buyer_message']);
        $req->setKfbz(empty($stdTrade['seller_memo']) ? '暂无' : $stdTrade['seller_memo']);
        $req->setPosOuterCode($stdTrade['pay_type']);
        $req->setCustomerid(config('hubclient.clients.qimen.customerid', 'adidas'));
        $req->setItem(json_encode([]));

        $this->data = $req->getApiParas();

        $this->qimenRequest = $req;

        return $this;
    }

    public function getExtendProps($stdTrade, $stdExchange)
    {
        $exchangeCreateXml = $this->getTransformer()->format($stdExchange);
        $extendProps = [
            'type' => 'exchangeCreate',
            'buyer_nick' => $stdTrade['buyer_nick'],
            'buyer_email' => $stdTrade['buyer_email'],
            'receiver_mobile' => $stdTrade['receiver_mobile'],
            'receiver_name' => $stdTrade['receiver_name'],
            'content' => $exchangeCreateXml
        ];

        return $extendProps;
    }

    /**
     * xml 组装的报文
     *
     * @return ExchangeCreateTransformer
     */
    public function getTransformer()
    {
        return app()->make(ExchangeCreateTransformer::class);
    }
}
