<?php
/**
 * TOP API: qimen.taobao.pos.items.synchronize request
 * 
 * @author auto create
 * @since 1.0, 2019.10.14
 */
class TaobaoPosItemsSynchronizeRequest
{
	/** 
	 * 操作
	 **/
	private $actionType;
	
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * null
	 **/
	private $item;
	
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
		return "qimen.taobao.pos.items.synchronize";
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
