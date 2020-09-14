<?php
/**
 * TOP API: qimen.taobao.pos.customer.synchronize request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosCustomerSynchronizeRequest
{
	/** 
	 * 操作
	 **/
	private $actionType;
	
	/** 
	 * null
	 **/
	private $customer;
	
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * 所有的总条数
	 **/
	private $total;
	
	private $apiParas = array();
	
	public function setActionType($actionType)
	{
		$this->actionType = $actionType;
		$this->apiParas["actionType"] = $actionType;
	}

	public function getActionType()
	{
		return $this->actionType;
	}

	public function setCustomer($customer)
	{
		$this->customer = $customer;
		$this->apiParas["customer"] = $customer;
	}

	public function getCustomer()
	{
		return $this->customer;
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

	public function setTotal($total)
	{
		$this->total = $total;
		$this->apiParas["total"] = $total;
	}

	public function getTotal()
	{
		return $this->total;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.customer.synchronize";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->actionType,"actionType");
		RequestCheckUtil::checkMaxLength($this->actionType,50,"actionType");
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->customerid,50,"customerid");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
