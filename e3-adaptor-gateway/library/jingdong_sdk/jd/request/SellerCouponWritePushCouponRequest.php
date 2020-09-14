<?php
class SellerCouponWritePushCouponRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.seller.coupon.write.pushCoupon";
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
         $this->apiParas["requestId"] = $requestId;
	}

	public function getRequestId(){
	  return $this->requestId;
	}

                                                                        		                                    	                   			private $pin;
    	                        
	public function setPin($pin){
		$this->pin = $pin;
         $this->apiParas["pin"] = $pin;
	}

	public function getPin(){
	  return $this->pin;
	}

                        	                        	                   			private $distrTime;
    	                        
	public function setDistrTime($distrTime){
		$this->distrTime = $distrTime;
         $this->apiParas["distrTime"] = $distrTime;
	}

	public function getDistrTime(){
	  return $this->distrTime;
	}

                        	                   			private $couponId;
    	                        
	public function setCouponId($couponId){
		$this->couponId = $couponId;
         $this->apiParas["couponId"] = $couponId;
	}

	public function getCouponId(){
	  return $this->couponId;
	}

                        	                   			private $uuid;
    	                        
	public function setUuid($uuid){
		$this->uuid = $uuid;
         $this->apiParas["uuid"] = $uuid;
	}

	public function getUuid(){
	  return $this->uuid;
	}

                        	                   			private $openIdBuyer;
    	                                                                        
	public function setOpenIdBuyer($openIdBuyer){
		$this->openIdBuyer = $openIdBuyer;
         $this->apiParas["open_id_buyer"] = $openIdBuyer;
	}

	public function getOpenIdBuyer(){
	  return $this->openIdBuyer;
	}

                            }





        
 

