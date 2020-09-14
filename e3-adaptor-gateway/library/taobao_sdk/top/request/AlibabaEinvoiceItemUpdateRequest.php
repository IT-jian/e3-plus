<?php
/**
 * TOP API: alibaba.einvoice.item.update request
 * 
 * @author auto create
 * @since 1.0, 2018.12.18
 */
class AlibabaEinvoiceItemUpdateRequest
{
	/** 
	 * 商品的开票名称，对应发票的货物劳务名称，值DELETE时表示删除
	 **/
	private $invoiceName;
	
	/** 
	 * 商品id，优先级高于outerId，商品必须归属于店铺，itemId和outerId不能同时为空
	 **/
	private $itemId;
	
	/** 
	 * 税收分类编码，需要精确到叶子节点，必须和taxRate同时修改或删除，值DELETE时表示删除
	 **/
	private $itemNo;
	
	/** 
	 * 商家外部商品id，如果outerId对应了多个天猫sku，则会更新所有的sku开票信息。itemId和outerId不能同时为空
	 **/
	private $outerId;
	
	/** 
	 * skuId，必须是itemId下的sku，填写skuId后，修改和删除sku的开票信息
	 **/
	private $skuId;
	
	/** 
	 * 规格型号，值DELETE时表示删除
	 **/
	private $specification;
	
	/** 
	 * 税率，可选值0，3，4，5，6，10，11，13， 16，17，必须和itemNo同时修改或删除,值为DELETE时表示删除
	 **/
	private $taxRate;
	
	/** 
	 * 单位，值DELETE时表示删除
	 **/
	private $unit;
	
	/** 
	 * 是否根据outerId更新所有对应sku的开票信息，true=更新，false=开票信息维护在发票平台；自动开票时，根据skuId获取outerId，再根据outerId查询开票信息。outerId不为空时必填
	 **/
	private $updateSku;
	
	/** 
	 * 0税率标识，只有税率为0的情况才有值，0=出口零税率，1=免税，2=不征收，3=普通零税率，值为DELETE时表示删除
	 **/
	private $zeroRateFlag;
	
	private $apiParas = array();
	
	public function setInvoiceName($invoiceName)
	{
		$this->invoiceName = $invoiceName;
		$this->apiParas["invoice_name"] = $invoiceName;
	}

	public function getInvoiceName()
	{
		return $this->invoiceName;
	}

	public function setItemId($itemId)
	{
		$this->itemId = $itemId;
		$this->apiParas["item_id"] = $itemId;
	}

	public function getItemId()
	{
		return $this->itemId;
	}

	public function setItemNo($itemNo)
	{
		$this->itemNo = $itemNo;
		$this->apiParas["item_no"] = $itemNo;
	}

	public function getItemNo()
	{
		return $this->itemNo;
	}

	public function setOuterId($outerId)
	{
		$this->outerId = $outerId;
		$this->apiParas["outer_id"] = $outerId;
	}

	public function getOuterId()
	{
		return $this->outerId;
	}

	public function setSkuId($skuId)
	{
		$this->skuId = $skuId;
		$this->apiParas["sku_id"] = $skuId;
	}

	public function getSkuId()
	{
		return $this->skuId;
	}

	public function setSpecification($specification)
	{
		$this->specification = $specification;
		$this->apiParas["specification"] = $specification;
	}

	public function getSpecification()
	{
		return $this->specification;
	}

	public function setTaxRate($taxRate)
	{
		$this->taxRate = $taxRate;
		$this->apiParas["tax_rate"] = $taxRate;
	}

	public function getTaxRate()
	{
		return $this->taxRate;
	}

	public function setUnit($unit)
	{
		$this->unit = $unit;
		$this->apiParas["unit"] = $unit;
	}

	public function getUnit()
	{
		return $this->unit;
	}

	public function setUpdateSku($updateSku)
	{
		$this->updateSku = $updateSku;
		$this->apiParas["update_sku"] = $updateSku;
	}

	public function getUpdateSku()
	{
		return $this->updateSku;
	}

	public function setZeroRateFlag($zeroRateFlag)
	{
		$this->zeroRateFlag = $zeroRateFlag;
		$this->apiParas["zero_rate_flag"] = $zeroRateFlag;
	}

	public function getZeroRateFlag()
	{
		return $this->zeroRateFlag;
	}

	public function getApiMethodName()
	{
		return "alibaba.einvoice.item.update";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->invoiceName,65,"invoiceName");
		RequestCheckUtil::checkMaxLength($this->itemNo,19,"itemNo");
		RequestCheckUtil::checkMaxLength($this->specification,20,"specification");
		RequestCheckUtil::checkMaxLength($this->taxRate,6,"taxRate");
		RequestCheckUtil::checkMaxLength($this->unit,10,"unit");
		RequestCheckUtil::checkMaxLength($this->zeroRateFlag,6,"zeroRateFlag");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
