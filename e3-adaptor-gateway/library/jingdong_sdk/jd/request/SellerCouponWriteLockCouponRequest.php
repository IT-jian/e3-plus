<?php
class SellerCouponWriteLockCouponRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.seller.coupon.write.lockCoupon";
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

                        	                   			private $time;
    	                        
	public function setTime($time){
		$this->time = $time;
         $this->apiParas["time"] = $time;
	}

	public function getTime(){
	  return $this->time;
	}

                                                                        		                                    	                   			private $purpose;
    	                        
	public function setPurpose($purpose){
		$this->purpose = $purpose;
         $this->apiParas["purpose"] = $purpose;
	}

	public function getPurpose(){
	  return $this->purpose;
	}

                        	                   			private $operateTime;
    	                        
	public function setOperateTime($operateTime){
		$this->operateTime = $operateTime;
         $this->apiParas["operateTime"] = $operateTime;
	}

	public function getOperateTime(){
	  return $this->operateTime;
	}

                        	                        	                   			private $couponId;
    	                        
	public function setCouponId($couponId){
		$this->couponId = $couponId;
         $this->apiParas["couponId"] = $couponId;
	}

	public function getCouponId(){
	  return $this->couponId;
	}

                            }





        
 

