<?php
class NbhouseRentQueryHotLineSaasRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.nbhouse.rent.queryHotLineSaas";
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
                                                        		                                    	                        	                   			private $phoneExtensionNum;
    	                        
	public function setPhoneExtensionNum($phoneExtensionNum){
		$this->phoneExtensionNum = $phoneExtensionNum;
         $this->apiParas["phoneExtensionNum"] = $phoneExtensionNum;
	}

	public function getPhoneExtensionNum(){
	  return $this->phoneExtensionNum;
	}

                        	                   			private $page;
    	                        
	public function setPage($page){
		$this->page = $page;
         $this->apiParas["page"] = $page;
	}

	public function getPage(){
	  return $this->page;
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





        
 

