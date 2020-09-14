<?php
/**
 * TOP API: alibaba.einvoice.paper.invalid request
 * 
 * @author auto create
 * @since 1.0, 2018.10.23
 */
class AlibabaEinvoicePaperInvalidRequest
{
	/** 
	 * 作废操作人
	 **/
	private $invalidOperator;
	
	/** 
	 * 作废类型, 0=空白发票(有残缺 的纸张发票，不能做为有效报销)作废, 1=已开发票作废
	 **/
	private $invalidType;
	
	/** 
	 * 发票代码，空白作废时必填
	 **/
	private $invoiceCode;
	
	/** 
	 * 发票号码，空白作废时必填
	 **/
	private $invoiceNo;
	
	/** 
	 * 销售方纳税人识别号
	 **/
	private $payeeRegisterNo;
	
	/** 
	 * 开票流水号
	 **/
	private $serialNo;
	
	private $apiParas = array();
	
	public function setInvalidOperator($invalidOperator)
	{
		$this->invalidOperator = $invalidOperator;
		$this->apiParas["invalid_operator"] = $invalidOperator;
	}

	public function getInvalidOperator()
	{
		return $this->invalidOperator;
	}

	public function setInvalidType($invalidType)
	{
		$this->invalidType = $invalidType;
		$this->apiParas["invalid_type"] = $invalidType;
	}

	public function getInvalidType()
	{
		return $this->invalidType;
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

	public function setSerialNo($serialNo)
	{
		$this->serialNo = $serialNo;
		$this->apiParas["serial_no"] = $serialNo;
	}

	public function getSerialNo()
	{
		return $this->serialNo;
	}

	public function getApiMethodName()
	{
		return "alibaba.einvoice.paper.invalid";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->invalidOperator,"invalidOperator");
		RequestCheckUtil::checkNotNull($this->invalidType,"invalidType");
		RequestCheckUtil::checkNotNull($this->payeeRegisterNo,"payeeRegisterNo");
		RequestCheckUtil::checkNotNull($this->serialNo,"serialNo");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
