<?php
/**
 * TOP API: qimen.taobao.pos.items.get request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosItemsGetRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * 最后修改的结束时间
	 **/
	private $endTime;
	
	/** 
	 * 商品SKU编码
	 **/
	private $itemCode;
	
	/** 
	 * 当前页
	 **/
	private $page;
	
	/** 
	 * 每页条数
	 **/
	private $pageSize;
	
	/** 
	 * 商品的SPU编码
	 **/
	private $productCode;
	
	/** 
	 * 最后修改的起始时间
	 **/
	private $startTime;
	
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

	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}

	public function getEndTime()
	{
		return $this->endTime;
	}

	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		$this->apiParas["itemCode"] = $itemCode;
	}

	public function getItemCode()
	{
		return $this->itemCode;
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

	public function setProductCode($productCode)
	{
		$this->productCode = $productCode;
		$this->apiParas["productCode"] = $productCode;
	}

	public function getProductCode()
	{
		return $this->productCode;
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
		return "qimen.taobao.pos.items.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->customerid,50,"customerid");
		RequestCheckUtil::checkMaxLength($this->endTime,19,"endTime");
		RequestCheckUtil::checkMaxLength($this->itemCode,50,"itemCode");
		RequestCheckUtil::checkNotNull($this->page,"page");
		RequestCheckUtil::checkNotNull($this->pageSize,"pageSize");
		RequestCheckUtil::checkMaxLength($this->productCode,50,"productCode");
		RequestCheckUtil::checkMaxLength($this->startTime,19,"startTime");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
