<?php
class CreateSkuSymbolBindRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.createSkuSymbolBind";
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
                                                        		                                    	                        	                        	                   			private $submitTime;
    	                        
	public function setSubmitTime($submitTime){
		$this->submitTime = $submitTime;
         $this->apiParas["submitTime"] = $submitTime;
	}

	public function getSubmitTime(){
	  return $this->submitTime;
	}

                        	                   			private $ownerName;
    	                        
	public function setOwnerName($ownerName){
		$this->ownerName = $ownerName;
         $this->apiParas["ownerName"] = $ownerName;
	}

	public function getOwnerName(){
	  return $this->ownerName;
	}

                        	                   			private $bussinessTypeEnum;
    	                        
	public function setBussinessTypeEnum($bussinessTypeEnum){
		$this->bussinessTypeEnum = $bussinessTypeEnum;
         $this->apiParas["bussinessTypeEnum"] = $bussinessTypeEnum;
	}

	public function getBussinessTypeEnum(){
	  return $this->bussinessTypeEnum;
	}

                                                 	                        	                                                                                                                                                                                                                                                                                        private $isSku;
                              public function setIsSku($isSku ){
                 $this->isSku=$isSku;
                 $this->apiParas["isSku"] = $isSku;
              }

              public function getIsSku(){
              	return $this->isSku;
              }
                                                                                                                                                                                                                                                                                                                                                               private $itemSecondCateCd;
                              public function setItemSecondCateCd($itemSecondCateCd ){
                 $this->itemSecondCateCd=$itemSecondCateCd;
                 $this->apiParas["itemSecondCateCd"] = $itemSecondCateCd;
              }

              public function getItemSecondCateCd(){
              	return $this->itemSecondCateCd;
              }
                                                                                                                                                                                                                                                                                                                       private $itemThirdCateCd;
                              public function setItemThirdCateCd($itemThirdCateCd ){
                 $this->itemThirdCateCd=$itemThirdCateCd;
                 $this->apiParas["itemThirdCateCd"] = $itemThirdCateCd;
              }

              public function getItemThirdCateCd(){
              	return $this->itemThirdCateCd;
              }
                                                                                                                                                                                                                                                                                                                       private $spuId;
                              public function setSpuId($spuId ){
                 $this->spuId=$spuId;
                 $this->apiParas["spuId"] = $spuId;
              }

              public function getSpuId(){
              	return $this->spuId;
              }
                                                                                                                                                                                                                                                                                                                       private $itemFirstCateCd;
                              public function setItemFirstCateCd($itemFirstCateCd ){
                 $this->itemFirstCateCd=$itemFirstCateCd;
                 $this->apiParas["itemFirstCateCd"] = $itemFirstCateCd;
              }

              public function getItemFirstCateCd(){
              	return $this->itemFirstCateCd;
              }
                                                                                                                                                                                                                                                                                                                       private $skuId;
                              public function setSkuId($skuId ){
                 $this->skuId=$skuId;
                 $this->apiParas["skuId"] = $skuId;
              }

              public function getSkuId(){
              	return $this->skuId;
              }
                                                                                                                                                                                                                                                                                                                       private $itemFourthCateCd;
                              public function setItemFourthCateCd($itemFourthCateCd ){
                 $this->itemFourthCateCd=$itemFourthCateCd;
                 $this->apiParas["itemFourthCateCd"] = $itemFourthCateCd;
              }

              public function getItemFourthCateCd(){
              	return $this->itemFourthCateCd;
              }
                                                                                                                                            }





        
 

