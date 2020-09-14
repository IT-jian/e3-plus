<?php
class FindSplitAccountsEntityRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.findSplitAccountsEntity";
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
                                                        		                                    	                   			private $splitId;
    	                        
	public function setSplitId($splitId){
		$this->splitId = $splitId;
         $this->apiParas["splitId"] = $splitId;
	}

	public function getSplitId(){
	  return $this->splitId;
	}

                        	                        	                   			private $storeId;
    	                        
	public function setStoreId($storeId){
		$this->storeId = $storeId;
         $this->apiParas["storeId"] = $storeId;
	}

	public function getStoreId(){
	  return $this->storeId;
	}

                        	                   			private $index;
    	                        
	public function setIndex($index){
		$this->index = $index;
         $this->apiParas["index"] = $index;
	}

	public function getIndex(){
	  return $this->index;
	}

                        	                   			private $pageSize;
    	                        
	public function setPageSize($pageSize){
		$this->pageSize = $pageSize;
         $this->apiParas["pageSize"] = $pageSize;
	}

	public function getPageSize(){
	  return $this->pageSize;
	}

                            }





        
 

