<?php
class OrderTrackGetTrackShowResultRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.order.track.getTrackShowResult";
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
                                                        		                                    	                   			private $userPin;
    	                        
	public function setUserPin($userPin){
		$this->userPin = $userPin;
         $this->apiParas["userPin"] = $userPin;
	}

	public function getUserPin(){
	  return $this->userPin;
	}

                        	                   			private $orderId;
    	                        
	public function setOrderId($orderId){
		$this->orderId = $orderId;
         $this->apiParas["orderId"] = $orderId;
	}

	public function getOrderId(){
	  return $this->orderId;
	}

                                            		                                    	                        	                        	                        	                   			private $userIP;
    	                        
	public function setUserIP($userIP){
		$this->userIP = $userIP;
         $this->apiParas["userIP"] = $userIP;
	}

	public function getUserIP(){
	  return $this->userIP;
	}

                                                        }





        
 

