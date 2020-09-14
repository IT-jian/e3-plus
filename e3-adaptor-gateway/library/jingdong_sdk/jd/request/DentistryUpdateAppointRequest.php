<?php
class DentistryUpdateAppointRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.dentistry.updateAppoint";
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

                        	                   			private $appointEndTime;
    	                        
	public function setAppointEndTime($appointEndTime){
		$this->appointEndTime = $appointEndTime;
         $this->apiParas["appointEndTime"] = $appointEndTime;
	}

	public function getAppointEndTime(){
	  return $this->appointEndTime;
	}

                        	                   			private $appointBeginTime;
    	                        
	public function setAppointBeginTime($appointBeginTime){
		$this->appointBeginTime = $appointBeginTime;
         $this->apiParas["appointBeginTime"] = $appointBeginTime;
	}

	public function getAppointBeginTime(){
	  return $this->appointBeginTime;
	}

                        	                   			private $jdAppointmentId;
    	                        
	public function setJdAppointmentId($jdAppointmentId){
		$this->jdAppointmentId = $jdAppointmentId;
         $this->apiParas["jdAppointmentId"] = $jdAppointmentId;
	}

	public function getJdAppointmentId(){
	  return $this->jdAppointmentId;
	}

                        	                   			private $appointmentNo;
    	                        
	public function setAppointmentNo($appointmentNo){
		$this->appointmentNo = $appointmentNo;
         $this->apiParas["appointmentNo"] = $appointmentNo;
	}

	public function getAppointmentNo(){
	  return $this->appointmentNo;
	}

                        	                   			private $storeId;
    	                        
	public function setStoreId($storeId){
		$this->storeId = $storeId;
         $this->apiParas["storeId"] = $storeId;
	}

	public function getStoreId(){
	  return $this->storeId;
	}

                        	                   			private $appointDate;
    	                        
	public function setAppointDate($appointDate){
		$this->appointDate = $appointDate;
         $this->apiParas["appointDate"] = $appointDate;
	}

	public function getAppointDate(){
	  return $this->appointDate;
	}

                        	                            }





        
 

