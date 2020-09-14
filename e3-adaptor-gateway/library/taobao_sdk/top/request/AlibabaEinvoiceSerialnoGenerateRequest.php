<?php
/**
 * TOP API: alibaba.einvoice.serialno.generate request
 * 
 * @author auto create
 * @since 1.0, 2018.07.25
 */
class AlibabaEinvoiceSerialnoGenerateRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "alibaba.einvoice.serialno.generate";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
