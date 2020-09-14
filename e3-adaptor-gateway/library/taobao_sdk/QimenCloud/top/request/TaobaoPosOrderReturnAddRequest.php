<?php
/**
 * TOP API: qimen.taobao.pos.order.return.add request
 * 
 * @author auto create
 * @since 1.0, 2019.02.18
 */
class TaobaoPosOrderReturnAddRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * 扩展字段
	 **/
	private $extendProps;
	
	/** 
	 * 退单信息
	 **/
	private $sellReturnRecord;
	
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
		$this->apiParas["extendProps"] = $extendProps;
	}

	public function getExtendProps()
	{
		return $this->extendProps;
	}

	public function setSellReturnRecord($sellReturnRecord)
	{
		$this->sellReturnRecord = $sellReturnRecord;
		$this->apiParas["sell_return_record"] = $sellReturnRecord;
	}

	public function getSellReturnRecord()
	{
		return $this->sellReturnRecord;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.order.return.add";
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
