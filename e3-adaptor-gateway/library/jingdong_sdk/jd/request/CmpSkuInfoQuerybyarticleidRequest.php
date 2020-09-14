<?php
class CmpSkuInfoQuerybyarticleidRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.cmp.sku.info.querybyarticleid";
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
                                    	                   			private $articleId;
    	                        
	public function setArticleId($articleId){
		$this->articleId = $articleId;
         $this->apiParas["articleId"] = $articleId;
	}

	public function getArticleId(){
	  return $this->articleId;
	}

                        	}





        
 

