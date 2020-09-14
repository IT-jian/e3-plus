<?php


namespace App\Services\Hub\Qimen;



class BaseRequest extends \App\Services\Hub\Adidas\BaseRequest
{
    public $format = "json";

    public $qimenRequest;

    public $shop;

    public function __toString()
    {
        $psrRequest = $this->getRequest();

        return self::psrRequestToString($psrRequest);
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->qimenRequest;
    }

    /**
     * @param mixed $shop
     * @return BaseRequest
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShop()
    {
        return $this->shop;
    }
}
