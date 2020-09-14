<?php
/**
 * TOP API: qimen.taobao.pos.update.wxorder request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosUpdateWxorderRequest
{
	/** 
	 * 取消类型
	 **/
	private $cancelType;
	
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * 订单号
	 **/
	private $orderCode;
	
	private $apiParas = array();
	
	public function setCancelType($cancelType)
	{
		$this->cancelType = $cancelType;
		$this->apiParas["cancelType"] = $cancelType;
	}

	public function getCancelType()
	{
		return $this->cancelType;
	}

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
		return "qimen.taobao.pos.update.wxorder";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->cancelType,"cancelType");
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->customerid,50,"customerid");
		RequestCheckUtil::checkNotNull($this->orderCode,"orderCode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
