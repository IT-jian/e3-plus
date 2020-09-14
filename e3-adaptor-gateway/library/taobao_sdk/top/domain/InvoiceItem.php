<?php

/**
 * 发票明细
 * @author auto create
 */
class InvoiceItem
{
	
	/** 
	 * 价税合计。(等于sumPrice和tax之和)
	 **/
	public $amount;
	
	/** 
	 * 淘宝子订单号
	 **/
	public $biz_order_id;
	
	/** 
	 * 是否运费行标识，true:运费行，false:非运费行
	 **/
	public $is_post_fee_row;
	
	/** 
	 * 发票项目名称（或商品名称）
	 **/
	public $item_name;
	
	/** 
	 * 单价，格式：100.00(不含税)
	 **/
	public $price;
	
	/** 
	 * 数量
	 **/
	public $quantity;
	
	/** 
	 * 发票行性质。0表示正常行，1表示折扣行，2表示被折扣行。比如充电器单价100元，折扣10元，则明细为2行，充电器行性质为2，折扣行性质为1。如果充电器没有折扣，则值应为0
	 **/
	public $row_type;
	
	/** 
	 * 规格型号,可选
	 **/
	public $specification;
	
	/** 
	 * 总价，格式：100.00(不含税)
	 **/
	public $sum_price;
	
	/** 
	 * 税额
	 **/
	public $tax;
	
	/** 
	 * 税率。税率只能为0或0.03或0.04或0.06或0.11或0.13或0.17
	 **/
	public $tax_rate;
	
	/** 
	 * 单位
	 **/
	public $unit;	
}
?>