<?php
class JingyiyueVenderapiUpdateappointtimeRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.jingyiyue.venderapi.updateappointtime";
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
                                                        		                                    	                   			private $sourceKey;
    	                        
	public function setSourceKey($sourceKey){
		$this->sourceKey = $sourceKey;
         $this->apiParas["sourceKey"] = $sourceKey;
	}

	public function getSourceKey(){
	  return $this->sourceKey;
	}

                        	                   			private $appointStartTime;
    	                        
	public function setAppointStartTime($appointStartTime){
		$this->appointStartTime = $appointStartTime;
         $this->apiParas["appointStartTime"] = $appointStartTime;
	}

	public function getAppointStartTime(){
	  return $this->appointStartTime;
	}

                        	                        	                   			private $appointOrderId;
    	                        
	public function setAppointOrderId($appointOrderId){
		$this->appointOrderId = $appointOrderId;
         $this->apiParas["appointOrderId"] = $appointOrderId;
	}

	public function getAppointOrderId(){
	  return $this->appointOrderId;
	}

                        	                   			private $venderOrderId;
    	                        
	public function setVenderOrderId($venderOrderId){
		$this->venderOrderId = $venderOrderId;
         $this->apiParas["venderOrderId"] = $venderOrderId;
	}

	public function getVenderOrderId(){
	  return $this->venderOrderId;
	}

                        	                   			private $appointEndTime;
    	                        
	public function setAppointEndTime($appointEndTime){
		$this->appointEndTime = $appointEndTime;
         $this->apiParas["appointEndTime"] = $appointEndTime;
	}

	public function getAppointEndTime(){
	  return $this->appointEndTime;
	}

                            }





        
 

