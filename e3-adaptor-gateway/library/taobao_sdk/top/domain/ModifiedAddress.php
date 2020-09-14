<?php

/**
 * 要修改的地址信息
 * @author auto create
 */
class ModifiedAddress
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
	 * 收货人姓名（可不填，无表示没有修改）
	 **/
	public $name;
	
	/** 
	 * 收货人电话（可不填，无表示没有修改）
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