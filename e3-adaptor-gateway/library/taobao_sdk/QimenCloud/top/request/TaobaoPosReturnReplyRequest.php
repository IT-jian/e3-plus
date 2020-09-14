<?php
/**
 * TOP API: qimen.taobao.pos.return.reply request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosReturnReplyRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * null
	 **/
	private $operator;
	
	/** 
	 * 退仓申请单单据编码
	 **/
	private $orderCode;
	
	/** 
	 * 单据类型
	 **/
	private $orderType;
	
	/** 
	 * 组织编码
	 **/
	private $orgCode;
	
	/** 
	 * 单据状态
	 **/
	private $status;
	
	/** 
	 * 提出申请的门店编码
	 **/
	private $storeCode;
	
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

	public function setOperator($operator)
	{
		$this->operator = $operator;
		$this->apiParas["operator"] = $operator;
	}

	public function getOperator()
	{
		return $this->operator;
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

	public function setOrderType($orderType)
	{
		$this->orderType = $orderType;
		$this->apiParas["orderType"] = $orderType;
	}

	public function getOrderType()
	{
		return $this->orderType;
	}

	public function setOrgCode($orgCode)
	{
		$this->orgCode = $orgCode;
		$this->apiParas["orgCode"] = $orgCode;
	}

	public function getOrgCode()
	{
		return $this->orgCode;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setStoreCode($storeCode)
	{
		$this->storeCode = $storeCode;
		$this->apiParas["storeCode"] = $storeCode;
	}

	public function getStoreCode()
	{
		return $this->storeCode;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.return.reply";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->customerid,50,"customerid");
		RequestCheckUtil::checkNotNull($this->orderCode,"orderCode");
		RequestCheckUtil::checkMaxLength($this->orderCode,50,"orderCode");
		RequestCheckUtil::checkNotNull($this->orderType,"orderType");
		RequestCheckUtil::checkMaxLength($this->orderType,50,"orderType");
		RequestCheckUtil::checkMaxLength($this->orgCode,50,"orgCode");
		RequestCheckUtil::checkNotNull($this->status,"status");
		RequestCheckUtil::checkMaxLength($this->status,50,"status");
		RequestCheckUtil::checkNotNull($this->storeCode,"storeCode");
		RequestCheckUtil::checkMaxLength($this->storeCode,50,"storeCode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
