<?php
class DentistryPushGoodsInfoRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.dentistry.pushGoodsInfo";
	}
	
	public function getApiParas(){
	    if(empty($this->apiParas)){
            return "{}";
        }
        return json_encode($this->apiParas);
	}
	
	public function check(){
		
	}
	
	public function putOtherTextParam($key, $value){
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}

    private  $version;

    public function setVersion($version){
        $this->version = $version;
    }

    public function getVersion(){
        return $this->version;
    }
                                                        		                                    	                   			private $goodsId;
    	                        
	public function setGoodsId($goodsId){
		$this->goodsId = $goodsId;
         $this->apiParas["goodsId"] = $goodsId;
	}

	public function getGoodsId(){
	  return $this->goodsId;
	}

                        	                   			private $channelType;
    	                        
	public function setChannelType($channelType){
		$this->channelType = $channelType;
         $this->apiParas["channelType"] = $channelType;
	}

	public function getChannelType(){
	  return $this->channelType;
	}

                                                 	                        	                                                                                                                                                                                                                                                                                                               private $itemName;
                              public function setItemName($itemName ){
                 $this->itemName=$itemName;
                 $this->apiParas["itemName"] = $itemName;
              }

              public function getItemName(){
              	return $this->itemName;
              }
                                                                                                                                                                                                                                                                                                                                              private $itemDesc;
                              public function setItemDesc($itemDesc ){
                 $this->itemDesc=$itemDesc;
                 $this->apiParas["itemDesc"] = $itemDesc;
              }

              public function getItemDesc(){
              	return $this->itemDesc;
              }
                                                                                                                                        	                   			private $operateType;
    	                        
	public function setOperateType($operateType){
		$this->operateType = $operateType;
         $this->apiParas["operateType"] = $operateType;
	}

	public function getOperateType(){
	  return $this->operateType;
	}

                        	                   			private $status;
    	                        
	public function setStatus($status){
		$this->status = $status;
         $this->apiParas["status"] = $status;
	}

	public function getStatus(){
	  return $this->status;
	}

                        	                   			private $goodsSuitable;
    	                        
	public function setGoodsSuitable($goodsSuitable){
		$this->goodsSuitable = $goodsSuitable;
         $this->apiParas["goodsSuitable"] = $goodsSuitable;
	}

	public function getGoodsSuitable(){
	  return $this->goodsSuitable;
	}

                        	                   			private $goodsPrice;
    	                        
	public function setGoodsPrice($goodsPrice){
		$this->goodsPrice = $goodsPrice;
         $this->apiParas["goodsPrice"] = $goodsPrice;
	}

	public function getGoodsPrice(){
	  return $this->goodsPrice;
	}

                        	                   			private $goodsFeature;
    	                        
	public function setGoodsFeature($goodsFeature){
		$this->goodsFeature = $goodsFeature;
         $this->apiParas["goodsFeature"] = $goodsFeature;
	}

	public function getGoodsFeature(){
	  return $this->goodsFeature;
	}

                        	                   			private $goodsName;
    	                        
	public function setGoodsName($goodsName){
		$this->goodsName = $goodsName;
         $this->apiParas["goodsName"] = $goodsName;
	}

	public function getGoodsName(){
	  return $this->goodsName;
	}

                        	                            }





        
 

