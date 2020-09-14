<?php
class QuerySymbolApprovalListRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.querySymbolApprovalList";
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
                                                        		                                    	                   			private $approvalStatus;
    	                        
	public function setApprovalStatus($approvalStatus){
		$this->approvalStatus = $approvalStatus;
         $this->apiParas["approvalStatus"] = $approvalStatus;
	}

	public function getApprovalStatus(){
	  return $this->approvalStatus;
	}

                        	                        	                   			private $pageSize;
    	                        
	public function setPageSize($pageSize){
		$this->pageSize = $pageSize;
         $this->apiParas["pageSize"] = $pageSize;
	}

	public function getPageSize(){
	  return $this->pageSize;
	}

                        	                   			private $searchKey;
    	                        
	public function setSearchKey($searchKey){
		$this->searchKey = $searchKey;
         $this->apiParas["searchKey"] = $searchKey;
	}

	public function getSearchKey(){
	  return $this->searchKey;
	}

                        	                   			private $bdsBindTypeEnum;
    	                        
	public function setBdsBindTypeEnum($bdsBindTypeEnum){
		$this->bdsBindTypeEnum = $bdsBindTypeEnum;
         $this->apiParas["bdsBindTypeEnum"] = $bdsBindTypeEnum;
	}

	public function getBdsBindTypeEnum(){
	  return $this->bdsBindTypeEnum;
	}

                        	                        	                   			private $pageNo;
    	                        
	public function setPageNo($pageNo){
		$this->pageNo = $pageNo;
         $this->apiParas["pageNo"] = $pageNo;
	}

	public function getPageNo(){
	  return $this->pageNo;
	}

                        	                        	                   			private $symbolName;
    	                        
	public function setSymbolName($symbolName){
		$this->symbolName = $symbolName;
         $this->apiParas["symbolName"] = $symbolName;
	}

	public function getSymbolName(){
	  return $this->symbolName;
	}

                            }





        
 

