<?php

/**
 * 订单原始收货地址信息
 * @author auto create
 */
class OriginalAddress
{
	
	/** 
	 * 详细地址，不带town信息，非必须有可能为空
	 **/
	public $address_detail;
	
	/** 
	 * 区
	 **/
	public $area;
	
	/** 
	 * 市
	 **/
	public $city;
	
	/** 
	 * 可不填，不填的情况下默认中国
	 **/
	public $country;
	
	/** 
	 * 原始收货人姓名
	 **/
	public $name;
	
	/** 
	 * 原始收货人电话
	 **/
	public $phone;
	
	/** 
	 * 邮编，非必须有可能为空
	 **/
	public $post_code;
	
	/** 
	 * 省份、州等
	 **/
	public $province;
	
	/** 
	 * 乡、镇、街道信息
	 **/
	public $town;	
}
?>