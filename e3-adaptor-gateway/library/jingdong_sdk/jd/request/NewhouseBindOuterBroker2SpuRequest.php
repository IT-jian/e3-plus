<?php
class NewhouseBindOuterBroker2SpuRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.newhouse.bindOuterBroker2Spu";
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
                                    	                   			private $paramInfo;
    	                        
	public function setParamInfo($paramInfo){
		$this->paramInfo = $paramInfo;
         $this->apiParas["paramInfo"] = $paramInfo;
	}

	public function getParamInfo(){
	  return $this->paramInfo;
	}

                        	}





        
 

