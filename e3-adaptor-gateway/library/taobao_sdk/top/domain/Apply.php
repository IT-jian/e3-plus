<?php

/**
 * 开票明细
 * @author auto create
 */
class Apply
{
	
	/** 
	 * 抬头类型，0=个人，1=企业
	 **/
	public $business_type;
	
	/** 
	 * 扩展属性
	 **/
	public $extend_props;
	
	/** 
	 * 发票申请创建时间
	 **/
	public $gmt_create;
	
	/** 
	 * 发票申请修改时间
	 **/
	public $gmt_modified_str;
	
	/** 
	 * 开票金额
	 **/
	public $invoice_amount;
	
	/** 
	 * 发票明细
	 **/
	public $invoice_items;
	
	/** 
	 * 发票种类，0=电子发票，1=纸质发票，2=专票，现在默认是0
	 **/
	public $invoice_kind;
	
	/** 
	 * 发票(开票)类型，蓝票blue,红票red，默认blue
	 **/
	public $invoice_type;
	
	/** 
	 * 买家备注
	 **/
	public $memo;
	
	/** 
	 * 购买方地址
	 **/
	public $payer_address;
	
	/** 
	 * 购买方开户银行
	 **/
	public $payer_bank;
	
	/** 
	 * 购买方开户行账号
	 **/
	public $payer_bankaccount;
	
	/** 
	 * 买家抬头
	 **/
	public $payer_name;
	
	/** 
	 * 购买方联系电话
	 **/
	public $payer_phone;
	
	/** 
	 * 买家税号
	 **/
	public $payer_register_no;
	
	/** 
	 * 电商平台代码,TB,TM,ALIPAY,JD
	 **/
	public $platform_code;
	
	/** 
	 * 电商平台对应的订单号
	 **/
	public $platform_tid;
	
	/** 
	 * 开票申请状态，0=已拒绝，1=申请中，2=已同意
	 **/
	public $status;
	
	/** 
	 * 不含税总金额
	 **/
	public $sum_price;
	
	/** 
	 * 总税额
	 **/
	public $sum_tax;
	
	/** 
	 * 开票申请的触发类型，buyer_payed=卖家已付款，sent_goods=卖家已发货，buyer_confirm=买家确认收货，refund_seller_confirm=卖家同意退款，invoice_supply=买家申请补开发票，invoice_change=买家申请改抬头，change_paper=电换纸
	 **/
	public $trigger_status;	
}
?>