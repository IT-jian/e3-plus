<?php


namespace App\Services\Platform\Jingdong\Client\Jos\Request;


use App\Services\Platform\Jingdong\Client\Jos\JosRequest;


/**
 * jingdong.pop.order.search ( 根据条件检索订单信息 )
 *
 * Class PopOrderGetRequest
 *
 * @package App\Services\Platform\Jingdong\Client\Jos\Request
 *
 *
 * jingdong_pop_order_get_responce.searchorderinfo_result.orderInfoList
 */
class PopOrderSearchRequest extends JosRequest
{
    /**
     * 接口名称
     *
     * @var string
     * @see https://open.jd.com/home/home#/doc/api?apiCateId=55&apiId=4247&apiName=jingdong.pop.order.get
     */
    protected $apiName = 'jingdong.pop.order.search';

    protected $paramKeys = [
        'start_date',
        'end_date',
        'order_state',
        'optional_fields',
        'page',
        'page_size',
        'sortType',
        'dateType',
    ];

    public function setStartDate($value) {
        $this->setData(['start_date' => $value], true);

        return $this;
    }

    public function setEndDate($value) {
        $this->setData(['end_date' => $value], true);

        return $this;
    }

    public function setOrderState($value) {
        $this->setData(['order_state' => $value], true);

        return $this;
    }

    public function setOptionalFields($value) {
        $this->setData(['optional_fields' => $value], true);

        return $this;
    }

    public function setPage($value) {
        $this->setData(['page' => $value], true);

        return $this;
    }

    public function setPageSize($value) {
        $this->setData(['page_size' => $value], true);

        return $this;
    }

    public function setSortType($value) {
        $this->setData(['sortType' => $value], true);

        return $this;
    }

    public function setDateType($value) {
        $this->setData(['dateType', $value], true);

        return $this;
    }

    public function check()
    {
        //RequestCheckUtil::checkNotNull($this->buId, "buId");
        //RequestCheckUtil::checkNotNull($this->operatePin, "operatePin");
        //RequestCheckUtil::checkNotNull($this->operateNick, "operateNick");
    }
}
