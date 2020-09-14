<?php
namespace App\Services\Platform\Taobao\Client\Top\Request;

use App\Services\Platform\Taobao\Client\Top\RequestCheckUtil;

/**
 * TOP API: alibaba.einvoice.detail.upload request
 *
 * @author auto create
 * @since 1.0, 2020.04.14
 */
class AlibabaEinvoiceDetailUploadRequest implements RequestContract
{
	/**
	 * 抬头类型，0=个人，1=企业
	 **/
	private $businessType;

	/**
	 * 发票校验码
	 **/
	private $checksum;

	/**
	 * 开票金额
	 **/
	private $invoiceAmount;

	/**
	 * 发票代码
	 **/
	private $invoiceCode;

	/**
	 * 开票日期
	 **/
	private $invoiceDate;

	/**
	 * 发票数据，电子发票上传PDF
	 **/
	private $invoiceFileData;

	/**
	 * 发票明细
	 **/
	private $invoiceItems;

	/**
	 * 发票种类，0=电子发票,1=纸质发票,2=专票
	 **/
	private $invoiceKind;

	/**
	 * 发票号码
	 **/
	private $invoiceNo;

	/**
	 * 发票类型，blue=蓝票，red=红票
	 **/
	private $invoiceType;

	/**
	 * 快递单号
	 **/
	private $logisticCode;

	/**
	 * 快递公司
	 **/
	private $logisticName;

	/**
	 * 是否需要物流，true：logistic_code和logistic_name必填，false：logistic_code和logistic_name不用填
	 **/
	private $needLogistic;

	/**
	 * 原蓝票发票代码，红票是必填
	 **/
	private $normalInvoiceCode;

	/**
	 * 原蓝票发票号码，红票是必填
	 **/
	private $normalInvoiceNo;

	/**
	 * 销售方纳税人识别号（税号）
	 **/
	private $payeeRegisterNo;

	/**
	 * 购买方地址，专票必填
	 **/
	private $payerAddress;

	/**
	 * 购买方银行及账号，专票必填
	 **/
	private $payerBankaccount;

	/**
	 * 购买方抬头
	 **/
	private $payerName;

	/**
	 * 购买方电话，专票必填
	 **/
	private $payerPhone;

	/**
	 * 购买方税号，企业抬头和专票时必填
	 **/
	private $payerRegisterNo;

	/**
	 * 电商平台代码。TB=淘宝 、TM=天猫 、JD=京东、DD=当当、PP=拍拍、YX=易讯、EBAY=ebay、QQ=QQ网购、AMAZON=亚马逊、SN=苏宁、GM=国美、WPH=唯品会、JM=聚美、LF=乐蜂、MGJ=蘑菇街、JS=聚尚、PX=拍鞋、YT=银泰、YHD=1号店、VANCL=凡客、YL=邮乐、YG=优购、1688=阿里巴巴、POS=POS门店、OTHER=其他, (只传英文编码)
	 **/
	private $platformCode;

	/**
	 * 订单号
	 **/
	private $platformTid;

	/**
	 * 收票人姓名，纸票必填
	 **/
	private $receiver;

	/**
	 * 收票人邮箱，仅电子发票会发送邮箱
	 **/
	private $receiverEmail;

	/**
	 * 收票人电话，纸票必填
	 **/
	private $receiverPhone;

	/**
	 * 不含税金额（合计）
	 **/
	private $sumPrice;

	/**
	 * 税额（合计）
	 **/
	private $sumTax;

	private $apiParas = array();

	public function setBusinessType($businessType)
	{
		$this->businessType = $businessType;
		$this->apiParas["business_type"] = $businessType;
	}

	public function getBusinessType()
	{
		return $this->businessType;
	}

	public function setChecksum($checksum)
	{
		$this->checksum = $checksum;
		$this->apiParas["checksum"] = $checksum;
	}

	public function getChecksum()
	{
		return $this->checksum;
	}

	public function setInvoiceAmount($invoiceAmount)
	{
		$this->invoiceAmount = $invoiceAmount;
		$this->apiParas["invoice_amount"] = $invoiceAmount;
	}

	public function getInvoiceAmount()
	{
		return $this->invoiceAmount;
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

	public function setInvoiceDate($invoiceDate)
	{
		$this->invoiceDate = $invoiceDate;
		$this->apiParas["invoice_date"] = $invoiceDate;
	}

	public function getInvoiceDate()
	{
		return $this->invoiceDate;
	}

	public function setInvoiceFileData($invoiceFileData)
	{
		$this->invoiceFileData = $invoiceFileData;
		$this->apiParas["invoice_file_data"] = $invoiceFileData;
	}

	public function getInvoiceFileData()
	{
		return $this->invoiceFileData;
	}

	public function setInvoiceItems($invoiceItems)
	{
		$this->invoiceItems = $invoiceItems;
		$this->apiParas["invoice_items"] = $invoiceItems;
	}

	public function getInvoiceItems()
	{
		return $this->invoiceItems;
	}

	public function setInvoiceKind($invoiceKind)
	{
		$this->invoiceKind = $invoiceKind;
		$this->apiParas["invoice_kind"] = $invoiceKind;
	}

	public function getInvoiceKind()
	{
		return $this->invoiceKind;
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

	public function setInvoiceType($invoiceType)
	{
		$this->invoiceType = $invoiceType;
		$this->apiParas["invoice_type"] = $invoiceType;
	}

	public function getInvoiceType()
	{
		return $this->invoiceType;
	}

	public function setLogisticCode($logisticCode)
	{
		$this->logisticCode = $logisticCode;
		$this->apiParas["logistic_code"] = $logisticCode;
	}

	public function getLogisticCode()
	{
		return $this->logisticCode;
	}

	public function setLogisticName($logisticName)
	{
		$this->logisticName = $logisticName;
		$this->apiParas["logistic_name"] = $logisticName;
	}

	public function getLogisticName()
	{
		return $this->logisticName;
	}

	public function setNeedLogistic($needLogistic)
	{
		$this->needLogistic = $needLogistic;
		$this->apiParas["need_logistic"] = $needLogistic;
	}

	public function getNeedLogistic()
	{
		return $this->needLogistic;
	}

	public function setNormalInvoiceCode($normalInvoiceCode)
	{
		$this->normalInvoiceCode = $normalInvoiceCode;
		$this->apiParas["normal_invoice_code"] = $normalInvoiceCode;
	}

	public function getNormalInvoiceCode()
	{
		return $this->normalInvoiceCode;
	}

	public function setNormalInvoiceNo($normalInvoiceNo)
	{
		$this->normalInvoiceNo = $normalInvoiceNo;
		$this->apiParas["normal_invoice_no"] = $normalInvoiceNo;
	}

	public function getNormalInvoiceNo()
	{
		return $this->normalInvoiceNo;
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

	public function setPayerAddress($payerAddress)
	{
		$this->payerAddress = $payerAddress;
		$this->apiParas["payer_address"] = $payerAddress;
	}

	public function getPayerAddress()
	{
		return $this->payerAddress;
	}

	public function setPayerBankaccount($payerBankaccount)
	{
		$this->payerBankaccount = $payerBankaccount;
		$this->apiParas["payer_bankaccount"] = $payerBankaccount;
	}

	public function getPayerBankaccount()
	{
		return $this->payerBankaccount;
	}

	public function setPayerName($payerName)
	{
		$this->payerName = $payerName;
		$this->apiParas["payer_name"] = $payerName;
	}

	public function getPayerName()
	{
		return $this->payerName;
	}

	public function setPayerPhone($payerPhone)
	{
		$this->payerPhone = $payerPhone;
		$this->apiParas["payer_phone"] = $payerPhone;
	}

	public function getPayerPhone()
	{
		return $this->payerPhone;
	}

	public function setPayerRegisterNo($payerRegisterNo)
	{
		$this->payerRegisterNo = $payerRegisterNo;
		$this->apiParas["payer_register_no"] = $payerRegisterNo;
	}

	public function getPayerRegisterNo()
	{
		return $this->payerRegisterNo;
	}

	public function setPlatformCode($platformCode)
	{
		$this->platformCode = $platformCode;
		$this->apiParas["platform_code"] = $platformCode;
	}

	public function getPlatformCode()
	{
		return $this->platformCode;
	}

	public function setPlatformTid($platformTid)
	{
		$this->platformTid = $platformTid;
		$this->apiParas["platform_tid"] = $platformTid;
	}

	public function getPlatformTid()
	{
		return $this->platformTid;
	}

	public function setReceiver($receiver)
	{
		$this->receiver = $receiver;
		$this->apiParas["receiver"] = $receiver;
	}

	public function getReceiver()
	{
		return $this->receiver;
	}

	public function setReceiverEmail($receiverEmail)
	{
		$this->receiverEmail = $receiverEmail;
		$this->apiParas["receiver_email"] = $receiverEmail;
	}

	public function getReceiverEmail()
	{
		return $this->receiverEmail;
	}

	public function setReceiverPhone($receiverPhone)
	{
		$this->receiverPhone = $receiverPhone;
		$this->apiParas["receiver_phone"] = $receiverPhone;
	}

	public function getReceiverPhone()
	{
		return $this->receiverPhone;
	}

	public function setSumPrice($sumPrice)
	{
		$this->sumPrice = $sumPrice;
		$this->apiParas["sum_price"] = $sumPrice;
	}

	public function getSumPrice()
	{
		return $this->sumPrice;
	}

	public function setSumTax($sumTax)
	{
		$this->sumTax = $sumTax;
		$this->apiParas["sum_tax"] = $sumTax;
	}

	public function getSumTax()
	{
		return $this->sumTax;
	}

	public function getApiMethodName()
	{
		return "alibaba.einvoice.detail.upload";
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function check()
	{

		RequestCheckUtil::checkNotNull($this->businessType,"businessType");
		RequestCheckUtil::checkNotNull($this->invoiceAmount,"invoiceAmount");
		RequestCheckUtil::checkNotNull($this->invoiceCode,"invoiceCode");
		RequestCheckUtil::checkMaxLength($this->invoiceCode,12,"invoiceCode");
		RequestCheckUtil::checkNotNull($this->invoiceDate,"invoiceDate");
		RequestCheckUtil::checkNotNull($this->invoiceKind,"invoiceKind");
		RequestCheckUtil::checkNotNull($this->invoiceNo,"invoiceNo");
		RequestCheckUtil::checkMaxLength($this->invoiceNo,8,"invoiceNo");
		RequestCheckUtil::checkNotNull($this->invoiceType,"invoiceType");
		RequestCheckUtil::checkMaxLength($this->normalInvoiceCode,12,"normalInvoiceCode");
		RequestCheckUtil::checkMaxLength($this->normalInvoiceNo,8,"normalInvoiceNo");
		RequestCheckUtil::checkNotNull($this->payeeRegisterNo,"payeeRegisterNo");
		RequestCheckUtil::checkMaxLength($this->payeeRegisterNo,21,"payeeRegisterNo");
		RequestCheckUtil::checkNotNull($this->payerName,"payerName");
		RequestCheckUtil::checkNotNull($this->platformCode,"platformCode");
		RequestCheckUtil::checkNotNull($this->platformTid,"platformTid");
		RequestCheckUtil::checkNotNull($this->sumPrice,"sumPrice");
		RequestCheckUtil::checkNotNull($this->sumTax,"sumTax");
	}

	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
