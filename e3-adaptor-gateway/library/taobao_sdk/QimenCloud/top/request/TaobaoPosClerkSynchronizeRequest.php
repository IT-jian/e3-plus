<?php
/**
 * TOP API: qimen.taobao.pos.clerk.synchronize request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosClerkSynchronizeRequest
{
	/** 
	 * 操作
	 **/
	private $actionType;
	
	/** 
	 * null
	 **/
	private $clerk;
	
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

	public function setClerk($clerk)
	{
		$this->clerk = $clerk;
		$this->apiParas["clerk"] = $clerk;
	}

	public function getClerk()
	{
		return $this->clerk;
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
		return "qimen.taobao.pos.clerk.synchronize";
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
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
