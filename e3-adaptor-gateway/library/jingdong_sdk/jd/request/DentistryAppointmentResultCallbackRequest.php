<?php
class DentistryAppointmentResultCallbackRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.dentistry.appointmentResultCallback";
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

                        	                   			private $resultType;
    	                        
	public function setResultType($resultType){
		$this->resultType = $resultType;
         $this->apiParas["resultType"] = $resultType;
	}

	public function getResultType(){
	  return $this->resultType;
	}

                        	                   			private $jdAppointmentId;
    	                        
	public function setJdAppointmentId($jdAppointmentId){
		$this->jdAppointmentId = $jdAppointmentId;
         $this->apiParas["jdAppointmentId"] = $jdAppointmentId;
	}

	public function getJdAppointmentId(){
	  return $this->jdAppointmentId;
	}

                        	                   			private $code;
    	                        
	public function setCode($code){
		$this->code = $code;
         $this->apiParas["code"] = $code;
	}

	public function getCode(){
	  return $this->code;
	}

                        	                   			private $resultDate;
    	                        
	public function setResultDate($resultDate){
		$this->resultDate = $resultDate;
         $this->apiParas["resultDate"] = $resultDate;
	}

	public function getResultDate(){
	  return $this->resultDate;
	}

                        	                   			private $appointmentNo;
    	                        
	public function setAppointmentNo($appointmentNo){
		$this->appointmentNo = $appointmentNo;
         $this->apiParas["appointmentNo"] = $appointmentNo;
	}

	public function getAppointmentNo(){
	  return $this->appointmentNo;
	}

                        	                   			private $msg;
    	                        
	public function setMsg($msg){
		$this->msg = $msg;
         $this->apiParas["msg"] = $msg;
	}

	public function getMsg(){
	  return $this->msg;
	}

                        	                            }





        
 

