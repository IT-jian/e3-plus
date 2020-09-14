<?php
/**
 * TOP API: qimen.taobao.pos.customer.get request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosCustomerGetRequest
{
	/** 
	 * 顾客编码
	 **/
	private $customerCode;
	
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * 最后修改的结束时间
	 **/
	private $endTime;
	
	/** 
	 * 当前页
	 **/
	private $page;
	
	/** 
	 * 每页条数
	 **/
	private $pageSize;
	
	/** 
	 * 最后修改的起始时间
	 **/
	private $startTime;
	
	private $apiParas = array();
	
	public function setCustomerCode($customerCode)
	{
		$this->customerCode = $customerCode;
		$this->apiParas["customerCode"] = $customerCode;
	}

	public function getCustomerCode()
	{
		return $this->customerCode;
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

	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}

	public function getEndTime()
	{
		return $this->endTime;
	}

	public function setPage($page)
	{
		$this->page = $page;
		$this->apiParas["page"] = $page;
	}

	public function getPage()
	{
		return $this->page;
	}

	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		$this->apiParas["pageSize"] = $pageSize;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}

	public function getStartTime()
	{
		return $this->startTime;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.customer.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->customerCode,50,"customerCode");
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->customerid,50,"customerid");
		RequestCheckUtil::checkMaxLength($this->endTime,50,"endTime");
		RequestCheckUtil::checkNotNull($this->page,"page");
		RequestCheckUtil::checkNotNull($this->pageSize,"pageSize");
		RequestCheckUtil::checkMaxLength($this->startTime,50,"startTime");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
