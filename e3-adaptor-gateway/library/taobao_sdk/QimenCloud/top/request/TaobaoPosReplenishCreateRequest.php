<?php
/**
 * TOP API: qimen.taobao.pos.replenish.create request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosReplenishCreateRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * null
	 **/
	private $orderInfo;
	
	/** 
	 * null
	 **/
	private $orderLine;
	
	private $apiParas = array();
	
	public function setCustomerid($customerid)
	{
		$this->customerid = $customerid;
		$this->apiParas["customerid"] = $customerid;
	}

	public function getCustomerid()
	{
		return $this->customerid;
	}

	public function setOrderInfo($orderInfo)
	{
		$this->orderInfo = $orderInfo;
		$this->apiParas["orderInfo"] = $orderInfo;
	}

	public function getOrderInfo()
	{
		return $this->orderInfo;
	}

	public function setOrderLine($orderLine)
	{
		$this->orderLine = $orderLine;
		$this->apiParas["orderLine"] = $orderLine;
	}

	public function getOrderLine()
	{
		return $this->orderLine;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.replenish.create";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->customerid,50,"customerid");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
