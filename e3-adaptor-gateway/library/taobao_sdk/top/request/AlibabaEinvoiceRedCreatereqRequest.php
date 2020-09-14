<?php
/**
 * TOP API: alibaba.einvoice.red.createreq request
 * 
 * @author auto create
 * @since 1.0, 2019.10.24
 */
class AlibabaEinvoiceRedCreatereqRequest
{
	/** 
	 * 蓝票流水号，优先级高于发票代码+发票号码
	 **/
	private $blueSerialNo;
	
	/** 
	 * 蓝票发票代码
	 **/
	private $invoiceCode;
	
	/** 
	 * 蓝票发票号码
	 **/
	private $invoiceNo;
	
	/** 
	 * 销售方税号
	 **/
	private $payeeRegisterNo;
	
	/** 
	 * 红票流水号
	 **/
	private $redSerialNo;
	
	private $apiParas = array();
	
	public function setBlueSerialNo($blueSerialNo)
	{
		$this->blueSerialNo = $blueSerialNo;
		$this->apiParas["blue_serial_no"] = $blueSerialNo;
	}

	public function getBlueSerialNo()
	{
		return $this->blueSerialNo;
	}

	public function setInvoiceCode($invoiceCode)
	{
		$this->invoiceCode = $invoiceCode;
		$this->apiParas["invoice_code"] = $invoiceCode;
	}

	public function getInvoiceCode()
	{
		return $this->invoiceCode;
	}

	public function setInvoiceNo($invoiceNo)
	{
		$this->invoiceNo = $invoiceNo;
		$this->apiParas["invoice_no"] = $invoiceNo;
	}

	public function getInvoiceNo()
	{
		return $this->invoiceNo;
	}

	public function setPayeeRegisterNo($payeeRegisterNo)
	{
		$this->payeeRegisterNo = $payeeRegisterNo;
		$this->apiParas["payee_register_no"] = $payeeRegisterNo;
	}

	public function getPayeeRegisterNo()
	{
		return $this->payeeRegisterNo;
	}

	public function setRedSerialNo($redSerialNo)
	{
		$this->redSerialNo = $redSerialNo;
		$this->apiParas["red_serial_no"] = $redSerialNo;
	}

	public function getRedSerialNo()
	{
		return $this->redSerialNo;
	}

	public function getApiMethodName()
	{
		return "alibaba.einvoice.red.createreq";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->blueSerialNo,20,"blueSerialNo");
		RequestCheckUtil::checkMaxLength($this->invoiceCode,16,"invoiceCode");
		RequestCheckUtil::checkMaxLength($this->invoiceNo,12,"invoiceNo");
		RequestCheckUtil::checkNotNull($this->payeeRegisterNo,"payeeRegisterNo");
		RequestCheckUtil::checkNotNull($this->redSerialNo,"redSerialNo");
		RequestCheckUtil::checkMaxLength($this->redSerialNo,20,"redSerialNo");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
