<?php
class HousedataGenerateHouseNewsRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.housedata.generateHouseNews";
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
                                    	                   			private $articleParam;
    	                        
	public function setArticleParam($articleParam){
		$this->articleParam = $articleParam;
         $this->apiParas["articleParam"] = $articleParam;
	}

	public function getArticleParam(){
	  return $this->articleParam;
	}

                        	                        	}





        
 

