<?php
class CmpSkuInfoQuerylistRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.cmp.sku.info.querylist";
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
                                    	                   			private $skuIds;
    	                        
	public function setSkuIds($skuIds){
		$this->skuIds = $skuIds;
         $this->apiParas["skuIds"] = $skuIds;
	}

	public function getSkuIds(){
	  return $this->skuIds;
	}

                        	}





        
 

