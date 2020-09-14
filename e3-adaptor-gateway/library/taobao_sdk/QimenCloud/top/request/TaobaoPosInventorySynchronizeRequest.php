<?php
/**
 * TOP API: qimen.taobao.pos.inventory.synchronize request
 * 
 * @author auto create
 * @since 1.0, 2018.09.07
 */
class TaobaoPosInventorySynchronizeRequest
{
	/** 
	 * customerid
	 **/
	private $customerid;
	
	/** 
	 * null
	 **/
	private $item;
	
	/** 
	 * 订单信息
	 **/
	private $orderInfo;
	
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

	public function setItem($item)
	{
		$this->item = $item;
		$this->apiParas["item"] = $item;
	}

	public function getItem()
	{
		return $this->item;
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

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.inventory.synchronize";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
