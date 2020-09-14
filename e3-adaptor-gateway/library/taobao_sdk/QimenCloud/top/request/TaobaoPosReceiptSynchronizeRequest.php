<?php
/**
 * TOP API: qimen.taobao.pos.receipt.synchronize request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosReceiptSynchronizeRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * null
	 **/
	private $receipt;
	
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

	public function setReceipt($receipt)
	{
		$this->receipt = $receipt;
		$this->apiParas["receipt"] = $receipt;
	}

	public function getReceipt()
	{
		return $this->receipt;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.receipt.synchronize";
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
