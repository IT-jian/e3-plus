<?php
/**
 * TOP API: alibaba.einvoice.apply.get request
 * 
 * @author auto create
 * @since 1.0, 2020.04.13
 */
class AlibabaEinvoiceApplyGetRequest
{
	/** 
	 * 开票申请ID，跟消息中的apply_id对应，传入applyId后，只会返回一条开票申请消息
	 **/
	private $applyId;
	
	/** 
	 * 平台订单号
	 **/
	private $platformTid;
	
	private $apiParas = array();
	
	public function setApplyId($applyId)
	{
		$this->applyId = $applyId;
		$this->apiParas["apply_id"] = $applyId;
	}

	public function getApplyId()
	{
		return $this->applyId;
	}

	public function setPlatformTid($platformTid)
	{
		$this->platformTid = $platformTid;
		$this->apiParas["platform_tid"] = $platformTid;
	}

	public function getPlatformTid()
	{
		return $this->platformTid;
	}

	public function getApiMethodName()
	{
		return "alibaba.einvoice.apply.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->platformTid,"platformTid");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
