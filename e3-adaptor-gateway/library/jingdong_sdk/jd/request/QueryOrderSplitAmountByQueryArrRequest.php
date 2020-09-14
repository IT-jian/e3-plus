<?php
class QueryOrderSplitAmountByQueryArrRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.queryOrderSplitAmountByQueryArr";
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
                                    	                   			private $id;
    	                        
	public function setId($id){
		$this->id = $id;
         $this->apiParas["id"] = $id;
	}

	public function getId(){
	  return $this->id;
	}

                                            		                                    	                   			private $systemName;
    	                        
	public function setSystemName($systemName){
		$this->systemName = $systemName;
         $this->apiParas["systemName"] = $systemName;
	}

	public function getSystemName(){
	  return $this->systemName;
	}

                        	                   			private $systemKey;
    	                        
	public function setSystemKey($systemKey){
		$this->systemKey = $systemKey;
         $this->apiParas["systemKey"] = $systemKey;
	}

	public function getSystemKey(){
	  return $this->systemKey;
	}

                                                    	                   	                    		private $queryTypes;
    	                        
	public function setQueryTypes($queryTypes){
		$this->queryTypes = $queryTypes;
         $this->apiParas["queryTypes"] = $queryTypes;
	}

	public function getQueryTypes(){
	  return $this->queryTypes;
	}

}





        
 

