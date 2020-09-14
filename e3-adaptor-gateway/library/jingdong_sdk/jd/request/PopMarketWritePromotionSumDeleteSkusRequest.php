<?php
class PopMarketWritePromotionSumDeleteSkusRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.pop.market.write.promotion.sum.deleteSkus";
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
                                                        		                                    	                        	                   			private $ip;
    	                        
	public function setIp($ip){
		$this->ip = $ip;
         $this->apiParas["ip"] = $ip;
	}

	public function getIp(){
	  return $this->ip;
	}

                        	                        	                        	                   			private $port;
    	                        
	public function setPort($port){
		$this->port = $port;
         $this->apiParas["port"] = $port;
	}

	public function getPort(){
	  return $this->port;
	}

                        	                   			private $requestId;
    	                                                            
	public function setRequestId($requestId){
		$this->requestId = $requestId;
         $this->apiParas["request_id"] = $requestId;
	}

	public function getRequestId(){
	  return $this->requestId;
	}

                        	                                                                        		                                    	                        	                   			private $rfId;
    	                        
	public function setRfId($rfId){
		$this->rfId = $rfId;
         $this->apiParas["rfId"] = $rfId;
	}

	public function getRfId(){
	  return $this->rfId;
	}

                        	                   			private $promoId;
    	                        
	public function setPromoId($promoId){
		$this->promoId = $promoId;
         $this->apiParas["promoId"] = $promoId;
	}

	public function getPromoId(){
	  return $this->promoId;
	}

                                                 	                        	                                                                                                                                                                                                                                                                                                               private $skuId;
                              public function setSkuId($skuId ){
                 $this->skuId=$skuId;
                 $this->apiParas["skuId"] = $skuId;
              }

              public function getSkuId(){
              	return $this->skuId;
              }
                                                                                                                                        	                   			private $operator1;
    	                        
	public function setOperator1($operator1){
		$this->operator1 = $operator1;
         $this->apiParas["operator1"] = $operator1;
	}

	public function getOperator1(){
	  return $this->operator1;
	}

                            }





        
 

