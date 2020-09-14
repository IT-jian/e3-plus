<?php
/**
 * TOP API: qimen.taobao.pos.update.salesrefund request
 * 
 * @author auto create
 * @since 1.0, 2018.11.26
 */
class TaobaoPosUpdateSalesrefundRequest
{
	/** 
	 * 路由参数
	 **/
	private $customerid;
	
	/** 
	 * 扩展属性
	 **/
	private $extendProps;
	
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

	public function setExtendProps($extendProps)
	{
		$this->extendProps = $extendProps;
		$this->apiParas["extend_props"] = $extendProps;
	}

	public function getExtendProps()
	{
		return $this->extendProps;
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
		return "qimen.taobao.pos.update.salesrefund";
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
