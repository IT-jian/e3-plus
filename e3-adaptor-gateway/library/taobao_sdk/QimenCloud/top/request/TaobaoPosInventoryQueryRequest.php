<?php
/**
 * TOP API: qimen.taobao.pos.inventory.query request
 * 
 * @author auto create
 * @since 1.0, 2018.08.03
 */
class TaobaoPosInventoryQueryRequest
{
	/** 
	 * 指定路由参数
	 **/
	private $customerid;
	
	/** 
	 * 商品sku编码
	 **/
	private $itemCode;
	
	/** 
	 * 商品spu编码
	 **/
	private $productCode;
	
	/** 
	 * 需要查询的门店(仓库)编码
	 **/
	private $storeCode;
	
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

	public function setItemCode($itemCode)
	{
		$this->itemCode = $itemCode;
		$this->apiParas["itemCode"] = $itemCode;
	}

	public function getItemCode()
	{
		return $this->itemCode;
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
		return "qimen.taobao.pos.inventory.query";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->customerid,"customerid");
		RequestCheckUtil::checkMaxLength($this->customerid,50,"customerid");
		RequestCheckUtil::checkMaxLength($this->itemCode,50,"itemCode");
		RequestCheckUtil::checkMaxLength($this->productCode,50,"productCode");
		RequestCheckUtil::checkNotNull($this->storeCode,"storeCode");
		RequestCheckUtil::checkMaxLength($this->storeCode,50,"storeCode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
