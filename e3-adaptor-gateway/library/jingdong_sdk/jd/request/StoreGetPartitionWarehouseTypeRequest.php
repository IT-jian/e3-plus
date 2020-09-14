<?php
class StoreGetPartitionWarehouseTypeRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.store.getPartitionWarehouseType";
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
                                    	                        	                   			private $seqNum;
    	                                                            
	public function setSeqNum($seqNum){
		$this->seqNum = $seqNum;
         $this->apiParas["seq_num"] = $seqNum;
	}

	public function getSeqNum(){
	  return $this->seqNum;
	}

}





        
 

