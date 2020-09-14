<?php
/**
 * TOP API: qimen.taobao.pos.inventory.report request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosInventoryReportRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * null
	 **/
	private $item;
	
	/** 
	 * null
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
		return "qimen.taobao.pos.inventory.report";
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
