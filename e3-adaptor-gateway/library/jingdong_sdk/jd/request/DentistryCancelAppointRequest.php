<?php
class DentistryCancelAppointRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.dentistry.cancelAppoint";
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
                                                        		                                    	                   			private $channelType;
    	                        
	public function setChannelType($channelType){
		$this->channelType = $channelType;
         $this->apiParas["channelType"] = $channelType;
	}

	public function getChannelType(){
	  return $this->channelType;
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

                        	                   			private $remark;
    	                        
	public function setRemark($remark){
		$this->remark = $remark;
         $this->apiParas["remark"] = $remark;
	}

	public function getRemark(){
	  return $this->remark;
	}

                        	                            }





        
 

