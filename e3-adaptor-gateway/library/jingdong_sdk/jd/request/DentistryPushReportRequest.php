<?php
class DentistryPushReportRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.dentistry.pushReport";
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
                                                        		                                    	                   			private $reportStr;
    	                        
	public function setReportStr($reportStr){
		$this->reportStr = $reportStr;
         $this->apiParas["reportStr"] = $reportStr;
	}

	public function getReportStr(){
	  return $this->reportStr;
	}

                        	                   			private $channelType;
    	                        
	public function setChannelType($channelType){
		$this->channelType = $channelType;
         $this->apiParas["channelType"] = $channelType;
	}

	public function getChannelType(){
	  return $this->channelType;
	}

                        	                   			private $appiontmentNo;
    	                        
	public function setAppiontmentNo($appiontmentNo){
		$this->appiontmentNo = $appiontmentNo;
         $this->apiParas["appiontmentNo"] = $appiontmentNo;
	}

	public function getAppiontmentNo(){
	  return $this->appiontmentNo;
	}

                        	                   			private $jdAppointmentId;
    	                        
	public function setJdAppointmentId($jdAppointmentId){
		$this->jdAppointmentId = $jdAppointmentId;
         $this->apiParas["jdAppointmentId"] = $jdAppointmentId;
	}

	public function getJdAppointmentId(){
	  return $this->jdAppointmentId;
	}

                        	                            }





        
 

