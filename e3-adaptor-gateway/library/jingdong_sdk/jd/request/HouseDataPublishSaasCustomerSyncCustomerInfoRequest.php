<?php
class HouseDataPublishSaasCustomerSyncCustomerInfoRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.house.data.publish.saas.customer.syncCustomerInfo";
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
                                                        		                                    	                   			private $tradeAmt;
    	                        
	public function setTradeAmt($tradeAmt){
		$this->tradeAmt = $tradeAmt;
         $this->apiParas["tradeAmt"] = $tradeAmt;
	}

	public function getTradeAmt(){
	  return $this->tradeAmt;
	}

                        	                   			private $city;
    	                        
	public function setCity($city){
		$this->city = $city;
         $this->apiParas["city"] = $city;
	}

	public function getCity(){
	  return $this->city;
	}

                        	                   			private $bookAmt;
    	                        
	public function setBookAmt($bookAmt){
		$this->bookAmt = $bookAmt;
         $this->apiParas["bookAmt"] = $bookAmt;
	}

	public function getBookAmt(){
	  return $this->bookAmt;
	}

                        	                        	                   			private $project;
    	                        
	public function setProject($project){
		$this->project = $project;
         $this->apiParas["project"] = $project;
	}

	public function getProject(){
	  return $this->project;
	}

                        	                   			private $tradeNums;
    	                        
	public function setTradeNums($tradeNums){
		$this->tradeNums = $tradeNums;
         $this->apiParas["tradeNums"] = $tradeNums;
	}

	public function getTradeNums(){
	  return $this->tradeNums;
	}

                        	                   			private $uuid;
    	                        
	public function setUuid($uuid){
		$this->uuid = $uuid;
         $this->apiParas["uuid"] = $uuid;
	}

	public function getUuid(){
	  return $this->uuid;
	}

                        	                   			private $customerName;
    	                        
	public function setCustomerName($customerName){
		$this->customerName = $customerName;
         $this->apiParas["customerName"] = $customerName;
	}

	public function getCustomerName(){
	  return $this->customerName;
	}

                                                 	                        	                                                                                                                                                                                                                                                                                                               private $attribute1;
                              public function setAttribute1($attribute1 ){
                 $this->attribute1=$attribute1;
                 $this->apiParas["attribute1"] = $attribute1;
              }

              public function getAttribute1(){
              	return $this->attribute1;
              }
                                                                                                                                        	                   			private $venderName;
    	                        
	public function setVenderName($venderName){
		$this->venderName = $venderName;
         $this->apiParas["venderName"] = $venderName;
	}

	public function getVenderName(){
	  return $this->venderName;
	}

                        	                   			private $customerPhone;
    	                        
	public function setCustomerPhone($customerPhone){
		$this->customerPhone = $customerPhone;
         $this->apiParas["customerPhone"] = $customerPhone;
	}

	public function getCustomerPhone(){
	  return $this->customerPhone;
	}

                        	                   			private $recordTime;
    	                        
	public function setRecordTime($recordTime){
		$this->recordTime = $recordTime;
         $this->apiParas["recordTime"] = $recordTime;
	}

	public function getRecordTime(){
	  return $this->recordTime;
	}

                        	                   			private $brokerPhone;
    	                        
	public function setBrokerPhone($brokerPhone){
		$this->brokerPhone = $brokerPhone;
         $this->apiParas["brokerPhone"] = $brokerPhone;
	}

	public function getBrokerPhone(){
	  return $this->brokerPhone;
	}

                                                 	                        	                                                                                                                                                                                                                                                                                                               private $attribute2;
                              public function setAttribute2($attribute2 ){
                 $this->attribute2=$attribute2;
                 $this->apiParas["attribute2"] = $attribute2;
              }

              public function getAttribute2(){
              	return $this->attribute2;
              }
                                                                                                                                        	                   			private $brokerName;
    	                        
	public function setBrokerName($brokerName){
		$this->brokerName = $brokerName;
         $this->apiParas["brokerName"] = $brokerName;
	}

	public function getBrokerName(){
	  return $this->brokerName;
	}

                                                 	                        	                                                                                                                                                                                                                                                                                                               private $attribute3;
                              public function setAttribute3($attribute3 ){
                 $this->attribute3=$attribute3;
                 $this->apiParas["attribute3"] = $attribute3;
              }

              public function getAttribute3(){
              	return $this->attribute3;
              }
                                                                                                                                        	                   			private $commisionAmt;
    	                        
	public function setCommisionAmt($commisionAmt){
		$this->commisionAmt = $commisionAmt;
         $this->apiParas["commisionAmt"] = $commisionAmt;
	}

	public function getCommisionAmt(){
	  return $this->commisionAmt;
	}

                        	                   			private $status;
    	                        
	public function setStatus($status){
		$this->status = $status;
         $this->apiParas["status"] = $status;
	}

	public function getStatus(){
	  return $this->status;
	}

                            }





        
 

