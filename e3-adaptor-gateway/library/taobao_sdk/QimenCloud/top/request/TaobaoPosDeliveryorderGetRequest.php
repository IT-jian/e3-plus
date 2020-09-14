<?php
/**
 * TOP API: qimen.taobao.pos.deliveryorder.get request
 * 
 * @author auto create
 * @since 1.0, 2019.05.08
 */
class TaobaoPosDeliveryorderGetRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * 门店发发货单创建结束时间
	 **/
	private $endTime;
	
	/** 
	 * 扩展字段
	 **/
	private $extendProps;
	
	/** 
	 * 收货人手机号
	 **/
	private $num;
	
	/** 
	 * 单据状态
	 **/
	private $orderStatus;
	
	/** 
	 * 订单号
	 **/
	private $orderid;
	
	/** 
	 * 当前页
	 **/
	private $page;
	
	/** 
	 * 每页条数
	 **/
	private $pageSize;
	
	/** 
	 * 门店发货单创建时间
	 **/
	private $startTime;
	
	/** 
	 * 门店编码
	 **/
	private $storeCode;
	
	/** 
	 * 提货方式
	 **/
	private $transportMode;
	
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

	public function setExtendProps($extendProps)
	{
		$this->extendProps = $extendProps;
		$this->apiParas["extendProps"] = $extendProps;
	}

	public function getExtendProps()
	{
		return $this->extendProps;
	}

	public function setNum($num)
	{
		$this->num = $num;
		$this->apiParas["num"] = $num;
	}

	public function getNum()
	{
		return $this->num;
	}

	public function setOrderStatus($orderStatus)
	{
		$this->orderStatus = $orderStatus;
		$this->apiParas["orderStatus"] = $orderStatus;
	}

	public function getOrderStatus()
	{
		return $this->orderStatus;
	}

	public function setOrderid($orderid)
	{
		$this->orderid = $orderid;
		$this->apiParas["orderid"] = $orderid;
	}

	public function getOrderid()
	{
		return $this->orderid;
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

	public function setTransportMode($transportMode)
	{
		$this->transportMode = $transportMode;
		$this->apiParas["transportMode"] = $transportMode;
	}

	public function getTransportMode()
	{
		return $this->transportMode;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.deliveryorder.get";
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
		RequestCheckUtil::checkNotNull($this->orderStatus,"orderStatus");
		RequestCheckUtil::checkMaxLength($this->orderStatus,50,"orderStatus");
		RequestCheckUtil::checkNotNull($this->page,"page");
		RequestCheckUtil::checkNotNull($this->pageSize,"pageSize");
		RequestCheckUtil::checkMaxLength($this->startTime,19,"startTime");
		RequestCheckUtil::checkMaxLength($this->storeCode,50,"storeCode");
		RequestCheckUtil::checkMaxLength($this->transportMode,50,"transportMode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
