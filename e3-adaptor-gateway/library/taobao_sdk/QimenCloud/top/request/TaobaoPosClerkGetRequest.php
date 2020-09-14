<?php
/**
 * TOP API: qimen.taobao.pos.clerk.get request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosClerkGetRequest
{
	/** 
	 * POS中的店员编码
	 **/
	private $clerkCode;
	
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
	
	/** 
	 * 门店(仓储)编码
	 **/
	private $storeCode;
	
	private $apiParas = array();
	
	public function setClerkCode($clerkCode)
	{
		$this->clerkCode = $clerkCode;
		$this->apiParas["clerkCode"] = $clerkCode;
	}

	public function getClerkCode()
	{
		return $this->clerkCode;
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
		return "qimen.taobao.pos.clerk.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->clerkCode,50,"clerkCode");
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->endTime,50,"endTime");
		RequestCheckUtil::checkNotNull($this->page,"page");
		RequestCheckUtil::checkNotNull($this->pageSize,"pageSize");
		RequestCheckUtil::checkMaxLength($this->startTime,50,"startTime");
		RequestCheckUtil::checkMaxLength($this->storeCode,50,"storeCode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
