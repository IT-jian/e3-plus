<?php
/**
 * TOP API: qimen.taobao.pos.salesorder.update request
 * 
 * @author auto create
 * @since 1.0, 2019.02.18
 */
class TaobaoPosSalesorderUpdateRequest
{
	/** 
	 * 作废人
	 **/
	private $canceler;
	
	/** 
	 * 作废
	 **/
	private $cancle;
	
	/** 
	 * 作废日期
	 **/
	private $cancleDate;
	
	/** 
	 * 确认
	 **/
	private $confirm;
	
	/** 
	 * 确认日期
	 **/
	private $confirmDate;
	
	/** 
	 * 确认人
	 **/
	private $confirmor;
	
	/** 
	 * 路由参数
	 **/
	private $customerid;
	
	/** 
	 * 取货
	 **/
	private $delivery;
	
	/** 
	 * 取货日期
	 **/
	private $deliveryDate;
	
	/** 
	 * 扩展字段
	 **/
	private $extendProps;
	
	/** 
	 * 完成
	 **/
	private $finish;
	
	/** 
	 * 完成日期
	 **/
	private $finishDate;
	
	/** 
	 * 单据编号
	 **/
	private $orderBillCode;
	
	/** 
	 * 快递代码
	 **/
	private $shippingCode;
	
	/** 
	 * 快递单号
	 **/
	private $shippingSn;
	
	/** 
	 * 状态类型
	 **/
	private $status;
	
	/** 
	 * 作废原因
	 **/
	private $zfMessage;
	
	/** 
	 * 作废类型
	 **/
	private $zfType;
	
	private $apiParas = array();
	
	public function setCanceler($canceler)
	{
		$this->canceler = $canceler;
		$this->apiParas["canceler"] = $canceler;
	}

	public function getCanceler()
	{
		return $this->canceler;
	}

	public function setCancle($cancle)
	{
		$this->cancle = $cancle;
		$this->apiParas["cancle"] = $cancle;
	}

	public function getCancle()
	{
		return $this->cancle;
	}

	public function setCancleDate($cancleDate)
	{
		$this->cancleDate = $cancleDate;
		$this->apiParas["cancleDate"] = $cancleDate;
	}

	public function getCancleDate()
	{
		return $this->cancleDate;
	}

	public function setConfirm($confirm)
	{
		$this->confirm = $confirm;
		$this->apiParas["confirm"] = $confirm;
	}

	public function getConfirm()
	{
		return $this->confirm;
	}

	public function setConfirmDate($confirmDate)
	{
		$this->confirmDate = $confirmDate;
		$this->apiParas["confirmDate"] = $confirmDate;
	}

	public function getConfirmDate()
	{
		return $this->confirmDate;
	}

	public function setConfirmor($confirmor)
	{
		$this->confirmor = $confirmor;
		$this->apiParas["confirmor"] = $confirmor;
	}

	public function getConfirmor()
	{
		return $this->confirmor;
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

	public function setDelivery($delivery)
	{
		$this->delivery = $delivery;
		$this->apiParas["delivery"] = $delivery;
	}

	public function getDelivery()
	{
		return $this->delivery;
	}

	public function setDeliveryDate($deliveryDate)
	{
		$this->deliveryDate = $deliveryDate;
		$this->apiParas["deliveryDate"] = $deliveryDate;
	}

	public function getDeliveryDate()
	{
		return $this->deliveryDate;
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

	public function setFinish($finish)
	{
		$this->finish = $finish;
		$this->apiParas["finish"] = $finish;
	}

	public function getFinish()
	{
		return $this->finish;
	}

	public function setFinishDate($finishDate)
	{
		$this->finishDate = $finishDate;
		$this->apiParas["finishDate"] = $finishDate;
	}

	public function getFinishDate()
	{
		return $this->finishDate;
	}

	public function setOrderBillCode($orderBillCode)
	{
		$this->orderBillCode = $orderBillCode;
		$this->apiParas["orderBillCode"] = $orderBillCode;
	}

	public function getOrderBillCode()
	{
		return $this->orderBillCode;
	}

	public function setShippingCode($shippingCode)
	{
		$this->shippingCode = $shippingCode;
		$this->apiParas["shippingCode"] = $shippingCode;
	}

	public function getShippingCode()
	{
		return $this->shippingCode;
	}

	public function setShippingSn($shippingSn)
	{
		$this->shippingSn = $shippingSn;
		$this->apiParas["shippingSn"] = $shippingSn;
	}

	public function getShippingSn()
	{
		return $this->shippingSn;
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

	public function setZfMessage($zfMessage)
	{
		$this->zfMessage = $zfMessage;
		$this->apiParas["zf_message"] = $zfMessage;
	}

	public function getZfMessage()
	{
		return $this->zfMessage;
	}

	public function setZfType($zfType)
	{
		$this->zfType = $zfType;
		$this->apiParas["zf_type"] = $zfType;
	}

	public function getZfType()
	{
		return $this->zfType;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.salesorder.update";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkNotNull($this->orderBillCode,"orderBillCode");
		RequestCheckUtil::checkNotNull($this->status,"status");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
