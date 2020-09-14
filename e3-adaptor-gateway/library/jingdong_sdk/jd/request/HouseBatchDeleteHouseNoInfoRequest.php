<?php
class HouseBatchDeleteHouseNoInfoRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.house.batchDeleteHouseNoInfo";
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
                                    	                   			private $houseNoInfo;
    	                        
	public function setHouseNoInfo($houseNoInfo){
		$this->houseNoInfo = $houseNoInfo;
         $this->apiParas["houseNoInfo"] = $houseNoInfo;
	}

	public function getHouseNoInfo(){
	  return $this->houseNoInfo;
	}

                        	}





        
 

