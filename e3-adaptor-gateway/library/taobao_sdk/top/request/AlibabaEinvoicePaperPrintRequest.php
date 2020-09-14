<?php
/**
 * TOP API: alibaba.einvoice.paper.print request
 * 
 * @author auto create
 * @since 1.0, 2018.10.08
 */
class AlibabaEinvoicePaperPrintRequest
{
	/** 
	 * 打印框设置，0=不弹打印设置框，1=弹出打印设置框
	 **/
	private $dialogSettingFlag;
	
	/** 
	 * 是否强制打印，一般发票只能打印一次，但是因为打印机发票号码与待打印发票号码不一致，导致打印错误，需要重新打印
	 **/
	private $forcePrint;
	
	/** 
	 * 销售方纳税人识别号
	 **/
	private $payeeRegisterNo;
	
	/** 
	 * 打印标记，0=打印发票；1=打印清单。发票明细超过8行时会生成清单页，需要打印清单。
	 **/
	private $printFlag;
	
	/** 
	 * 开票流水号
	 **/
	private $serialNo;
	
	private $apiParas = array();
	
	public function setDialogSettingFlag($dialogSettingFlag)
	{
		$this->dialogSettingFlag = $dialogSettingFlag;
		$this->apiParas["dialog_setting_flag"] = $dialogSettingFlag;
	}

	public function getDialogSettingFlag()
	{
		return $this->dialogSettingFlag;
	}

	public function setForcePrint($forcePrint)
	{
		$this->forcePrint = $forcePrint;
		$this->apiParas["force_print"] = $forcePrint;
	}

	public function getForcePrint()
	{
		return $this->forcePrint;
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

	public function setPrintFlag($printFlag)
	{
		$this->printFlag = $printFlag;
		$this->apiParas["print_flag"] = $printFlag;
	}

	public function getPrintFlag()
	{
		return $this->printFlag;
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
		return "alibaba.einvoice.paper.print";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->dialogSettingFlag,"dialogSettingFlag");
		RequestCheckUtil::checkNotNull($this->forcePrint,"forcePrint");
		RequestCheckUtil::checkNotNull($this->payeeRegisterNo,"payeeRegisterNo");
		RequestCheckUtil::checkNotNull($this->printFlag,"printFlag");
		RequestCheckUtil::checkNotNull($this->serialNo,"serialNo");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
