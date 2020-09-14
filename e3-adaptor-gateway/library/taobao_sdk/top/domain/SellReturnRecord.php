<?php

/**
 * 退单信息
 * @author auto create
 */
class SellReturnRecord
{
	
	/** 
	 * 退单商品信息
	 **/
	public $order_return_goods;
	
	/** 
	 * 订单编号
	 **/
	public $order_sn;
	
	/** 
	 * 退货仓库代码
	 **/
	public $return_ck_code;
	
	/** 
	 * 退货库位代码
	 **/
	public $return_kw_code;
	
	/** 
	 * 退单类型（1退货2追件3拒收）
	 **/
	public $return_type;	
}
?>