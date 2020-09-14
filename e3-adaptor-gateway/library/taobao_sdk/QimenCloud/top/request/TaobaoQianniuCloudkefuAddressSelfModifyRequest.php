<?php
/**
 * TOP API: qimen.taobao.qianniu.cloudkefu.address.self.modify request
 * 
 * @author auto create
 * @since 1.0, 2018.08.23
 */
class TaobaoQianniuCloudkefuAddressSelfModifyRequest
{
	/** 
	 * 交易订单ID
	 **/
	private $bizOrderId;
	
	/** 
	 * 买家账号名
	 **/
	private $buyerNick;
	
	/** 
	 * 要修改的地址信息
	 **/
	private $modifiedAddress;
	
	/** 
	 * 订单原始收货地址信息
	 **/
	private $originalAddress;
	
	/** 
	 * 店铺主账号
	 **/
	private $sellerNick;
	
	private $apiParas = array();
	
	public function setBizOrderId($bizOrderId)
	{
		$this->bizOrderId = $bizOrderId;
		$this->apiParas["bizOrderId"] = $bizOrderId;
	}

	public function getBizOrderId()
	{
		return $this->bizOrderId;
	}

	public function setBuyerNick($buyerNick)
	{
		$this->buyerNick = $buyerNick;
		$this->apiParas["buyerNick"] = $buyerNick;
	}

	public function getBuyerNick()
	{
		return $this->buyerNick;
	}

	public function setModifiedAddress($modifiedAddress)
	{
		$this->modifiedAddress = $modifiedAddress;
		$this->apiParas["modifiedAddress"] = $modifiedAddress;
	}

	public function getModifiedAddress()
	{
		return $this->modifiedAddress;
	}

	public function setOriginalAddress($originalAddress)
	{
		$this->originalAddress = $originalAddress;
		$this->apiParas["originalAddress"] = $originalAddress;
	}

	public function getOriginalAddress()
	{
		return $this->originalAddress;
	}

	public function setSellerNick($sellerNick)
	{
		$this->sellerNick = $sellerNick;
		$this->apiParas["sellerNick"] = $sellerNick;
	}

	public function getSellerNick()
	{
		return $this->sellerNick;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.qianniu.cloudkefu.address.self.modify";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->bizOrderId,"bizOrderId");
		RequestCheckUtil::checkNotNull($this->buyerNick,"buyerNick");
		RequestCheckUtil::checkNotNull($this->sellerNick,"sellerNick");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
