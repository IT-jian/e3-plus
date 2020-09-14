<?php
class DeleteSplitAccountsRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.deleteSplitAccounts";
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

                        	                            }





        
 

