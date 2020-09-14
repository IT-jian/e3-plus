<?php
/**
 * TOP API: qimen.taobao.pos.update.wxrefund request
 * 
 * @author auto create
 * @since 1.0, 2018.01.17
 */
class TaobaoPosUpdateWxrefundRequest
{
	/** 
	 * 货主编号
	 **/
	private $customerid;
	
	/** 
	 * 订单号
	 **/
	private $orderCode;
	
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

	public function setOrderCode($orderCode)
	{
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}

	public function getOrderCode()
	{
		return $this->orderCode;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.update.wxrefund";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkNotNull($this->orderCode,"orderCode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
