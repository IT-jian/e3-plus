<?php
class OmnicOrderSavesnRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.omnic.order.savesn";
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
                                                        		                                    	                   			private $authKey;
    	                        
	public function setAuthKey($authKey){
		$this->authKey = $authKey;
         $this->apiParas["authKey"] = $authKey;
	}

	public function getAuthKey(){
	  return $this->authKey;
	}

                        	                        	                                            		                                    	                   			private $orderId;
    	                        
	public function setOrderId($orderId){
		$this->orderId = $orderId;
         $this->apiParas["orderId"] = $orderId;
	}

	public function getOrderId(){
	  return $this->orderId;
	}

                        	                   			private $storeId;
    	                        
	public function setStoreId($storeId){
		$this->storeId = $storeId;
         $this->apiParas["storeId"] = $storeId;
	}

	public function getStoreId(){
	  return $this->storeId;
	}

                        	                   			private $venderStoreId;
    	                        
	public function setVenderStoreId($venderStoreId){
		$this->venderStoreId = $venderStoreId;
         $this->apiParas["venderStoreId"] = $venderStoreId;
	}

	public function getVenderStoreId(){
	  return $this->venderStoreId;
	}

                                                 	                        	                                                                                                                                                                                                                                                                                                               private $skuId;
                              public function setSkuId($skuId ){
                 $this->skuId=$skuId;
                 $this->apiParas["skuId"] = $skuId;
              }

              public function getSkuId(){
              	return $this->skuId;
              }
                                                                                                                                                                                                                                                                                                                                              private $outSkuId;
                              public function setOutSkuId($outSkuId ){
                 $this->outSkuId=$outSkuId;
                 $this->apiParas["outSkuId"] = $outSkuId;
              }

              public function getOutSkuId(){
              	return $this->outSkuId;
              }
                                                                                                                                                                                                                                                                                                                                              private $sn;
                              public function setSn($sn ){
                 $this->sn=$sn;
                 $this->apiParas["sn"] = $sn;
              }

              public function getSn(){
              	return $this->sn;
              }
                                                                                                                                                                        }





        
 

