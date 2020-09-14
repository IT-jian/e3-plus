<?php

namespace App\Services\Platform\Taobao\Qimen\Top\Request;


use App\Services\Platform\Taobao\Client\Top\RequestCheckUtil;


/**
 * TOP API: qimen.taobao.pos.weborder.sync request
 *
 * @author auto create
 * @since 1.0, 2019.10.16
 */
class TaobaoPosWeborderSyncRequest
{
	/**
	 * 街道
	 **/
	private $addres;

	/**
	 * 营业日期
	 **/
	private $billTime;

	/**
	 * 市
	 **/
	private $city;

	/**
	 * 制单人
	 **/
	private $creater;

	/**
	 * 制单日期
	 **/
	private $creationDate;

	/**
	 * 路由参数
	 **/
	private $customerid;

	/**
	 * 折扣
	 **/
	private $discount;

	/**
	 * 区
	 **/
	private $district;

	/**
	 * 扩展属性
	 **/
	private $extendProps;

	/**
	 * '顾客留言
	 **/
	private $gkly;

	/**
	 * 商品明细
	 **/
	private $item;

	/**
	 * '客服备注
	 **/
	private $kfbz;

	/**
	 * '来源渠道代码
	 **/
	private $lyorgDm;

	/**
	 * '来源渠道名称
	 **/
	private $lyorgMc;

	/**
	 * 来源平台
	 **/
	private $lypt;

	/**
	 * '来源店铺代码
	 **/
	private $lyzdDm;

	/**
	 * '来源店铺名称
	 **/
	private $lyzdMc;

	/**
	 * 标准价格
	 **/
	private $money;

	/**
	 * 姓名
	 **/
	private $name;

	/**
	 * 备注
	 **/
	private $note;

	/**
	 * 单据编号
	 **/
	private $orderBillCode;

	/**
	 * 原单据号
	 **/
	private $orderWebCod;

	/**
	 * 支付方式
	 **/
	private $paymethod;

	/**
	 * 手机号
	 **/
	private $phone;

	/**
	 * '结算代码
	 **/
	private $posOuterCode;

	/**
	 * 省
	 **/
	private $province;

	/**
	 * 数量
	 **/
	private $quantity;

	/**
	 * 金额
	 **/
	private $realMoney;

	/**
	 * 店员代码
	 **/
	private $salerEmployeeNo;

	/**
	 * 快递地址
	 **/
	private $shippingAddress;

	/**
	 * 快递代码
	 **/
	private $shippingCode;

	/**
	 * 快递单号
	 **/
	private $shippingSn;

	/**
	 * 门店代码
	 **/
	private $shopCode;

	/**
	 * 来源系统
	 **/
	private $system;

	/**
	 * 退单
	 **/
	private $thAct;

	/**
	 * '下单门店代码
	 **/
	private $xdzdDm;

	protected $apiParas = array();

	public function setAddres($addres)
	{
		$this->addres = $addres;
		$this->apiParas["addres"] = $addres;
	}

	public function getAddres()
	{
		return $this->addres;
	}

	public function setBillTime($billTime)
	{
		$this->billTime = $billTime;
		$this->apiParas["billTime"] = $billTime;
	}

	public function getBillTime()
	{
		return $this->billTime;
	}

	public function setCity($city)
	{
		$this->city = $city;
		$this->apiParas["city"] = $city;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function setCreater($creater)
	{
		$this->creater = $creater;
		$this->apiParas["creater"] = $creater;
	}

	public function getCreater()
	{
		return $this->creater;
	}

	public function setCreationDate($creationDate)
	{
		$this->creationDate = $creationDate;
		$this->apiParas["creationDate"] = $creationDate;
	}

	public function getCreationDate()
	{
		return $this->creationDate;
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

	public function setDiscount($discount)
	{
		$this->discount = $discount;
		$this->apiParas["discount"] = $discount;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function setDistrict($district)
	{
		$this->district = $district;
		$this->apiParas["district"] = $district;
	}

	public function getDistrict()
	{
		return $this->district;
	}

	public function setExtendProps($extendProps)
	{
		$this->extendProps = $extendProps;
		$this->apiParas["extend_props"] = $extendProps;
	}

	public function getExtendProps()
	{
		return $this->extendProps;
	}

	public function setGkly($gkly)
	{
		$this->gkly = $gkly;
		$this->apiParas["gkly"] = $gkly;
	}

	public function getGkly()
	{
		return $this->gkly;
	}

	public function setItem($item)
	{
		$this->item = $item;
		$this->apiParas["item"] = $item;
	}

	public function getItem()
	{
		return $this->item;
	}

	public function setKfbz($kfbz)
	{
		$this->kfbz = $kfbz;
		$this->apiParas["kfbz"] = $kfbz;
	}

	public function getKfbz()
	{
		return $this->kfbz;
	}

	public function setLyorgDm($lyorgDm)
	{
		$this->lyorgDm = $lyorgDm;
		$this->apiParas["lyorg_dm"] = $lyorgDm;
	}

	public function getLyorgDm()
	{
		return $this->lyorgDm;
	}

	public function setLyorgMc($lyorgMc)
	{
		$this->lyorgMc = $lyorgMc;
		$this->apiParas["lyorg_mc"] = $lyorgMc;
	}

	public function getLyorgMc()
	{
		return $this->lyorgMc;
	}

	public function setLypt($lypt)
	{
		$this->lypt = $lypt;
		$this->apiParas["lypt"] = $lypt;
	}

	public function getLypt()
	{
		return $this->lypt;
	}

	public function setLyzdDm($lyzdDm)
	{
		$this->lyzdDm = $lyzdDm;
		$this->apiParas["lyzd_dm"] = $lyzdDm;
	}

	public function getLyzdDm()
	{
		return $this->lyzdDm;
	}

	public function setLyzdMc($lyzdMc)
	{
		$this->lyzdMc = $lyzdMc;
		$this->apiParas["lyzd_mc"] = $lyzdMc;
	}

	public function getLyzdMc()
	{
		return $this->lyzdMc;
	}

	public function setMoney($money)
	{
		$this->money = $money;
		$this->apiParas["money"] = $money;
	}

	public function getMoney()
	{
		return $this->money;
	}

	public function setName($name)
	{
		$this->name = $name;
		$this->apiParas["name"] = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setNote($note)
	{
		$this->note = $note;
		$this->apiParas["note"] = $note;
	}

	public function getNote()
	{
		return $this->note;
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

	public function setOrderWebCod($orderWebCod)
	{
		$this->orderWebCod = $orderWebCod;
		$this->apiParas["orderWebCod"] = $orderWebCod;
	}

	public function getOrderWebCod()
	{
		return $this->orderWebCod;
	}

	public function setPaymethod($paymethod)
	{
		$this->paymethod = $paymethod;
		$this->apiParas["paymethod"] = $paymethod;
	}

	public function getPaymethod()
	{
		return $this->paymethod;
	}

	public function setPhone($phone)
	{
		$this->phone = $phone;
		$this->apiParas["phone"] = $phone;
	}

	public function getPhone()
	{
		return $this->phone;
	}

	public function setPosOuterCode($posOuterCode)
	{
		$this->posOuterCode = $posOuterCode;
		$this->apiParas["pos_outer_code"] = $posOuterCode;
	}

	public function getPosOuterCode()
	{
		return $this->posOuterCode;
	}

	public function setProvince($province)
	{
		$this->province = $province;
		$this->apiParas["province"] = $province;
	}

	public function getProvince()
	{
		return $this->province;
	}

	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		$this->apiParas["quantity"] = $quantity;
	}

	public function getQuantity()
	{
		return $this->quantity;
	}

	public function setRealMoney($realMoney)
	{
		$this->realMoney = $realMoney;
		$this->apiParas["realMoney"] = $realMoney;
	}

	public function getRealMoney()
	{
		return $this->realMoney;
	}

	public function setSalerEmployeeNo($salerEmployeeNo)
	{
		$this->salerEmployeeNo = $salerEmployeeNo;
		$this->apiParas["salerEmployeeNo"] = $salerEmployeeNo;
	}

	public function getSalerEmployeeNo()
	{
		return $this->salerEmployeeNo;
	}

	public function setShippingAddress($shippingAddress)
	{
		$this->shippingAddress = $shippingAddress;
		$this->apiParas["shippingAddress"] = $shippingAddress;
	}

	public function getShippingAddress()
	{
		return $this->shippingAddress;
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

	public function setShopCode($shopCode)
	{
		$this->shopCode = $shopCode;
		$this->apiParas["shopCode"] = $shopCode;
	}

	public function getShopCode()
	{
		return $this->shopCode;
	}

	public function setSystem($system)
	{
		$this->system = $system;
		$this->apiParas["system"] = $system;
	}

	public function getSystem()
	{
		return $this->system;
	}

	public function setThAct($thAct)
	{
		$this->thAct = $thAct;
		$this->apiParas["th_act"] = $thAct;
	}

	public function getThAct()
	{
		return $this->thAct;
	}

	public function setXdzdDm($xdzdDm)
	{
		$this->xdzdDm = $xdzdDm;
		$this->apiParas["xdzd_dm"] = $xdzdDm;
	}

	public function getXdzdDm()
	{
		return $this->xdzdDm;
	}

	public function getApiMethodName()
	{
		return "qimen.taobao.pos.weborder.sync";
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function check()
	{
		RequestCheckUtil::checkNotNull($this->billTime,"billTime");
		RequestCheckUtil::checkNotNull($this->discount,"discount");
		RequestCheckUtil::checkNotNull($this->gkly,"gkly");
		RequestCheckUtil::checkNotNull($this->kfbz,"kfbz");
		RequestCheckUtil::checkNotNull($this->lyorgDm,"lyorgDm");
		RequestCheckUtil::checkNotNull($this->lyorgMc,"lyorgMc");
		RequestCheckUtil::checkNotNull($this->lypt,"lypt");
		RequestCheckUtil::checkNotNull($this->lyzdDm,"lyzdDm");
		RequestCheckUtil::checkNotNull($this->lyzdMc,"lyzdMc");
		RequestCheckUtil::checkNotNull($this->money,"money");
		RequestCheckUtil::checkNotNull($this->name,"name");
		RequestCheckUtil::checkNotNull($this->orderBillCode,"orderBillCode");
		RequestCheckUtil::checkNotNull($this->orderWebCod,"orderWebCod");
		RequestCheckUtil::checkNotNull($this->paymethod,"paymethod");
		RequestCheckUtil::checkNotNull($this->phone,"phone");
		RequestCheckUtil::checkNotNull($this->posOuterCode,"posOuterCode");
		RequestCheckUtil::checkNotNull($this->quantity,"quantity");
		RequestCheckUtil::checkNotNull($this->realMoney,"realMoney");
		RequestCheckUtil::checkNotNull($this->shopCode,"shopCode");
		RequestCheckUtil::checkNotNull($this->system,"system");
		RequestCheckUtil::checkNotNull($this->thAct,"thAct");
		RequestCheckUtil::checkNotNull($this->xdzdDm,"xdzdDm");
	}

	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
