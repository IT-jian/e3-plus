<?php
class AdsDspRtbKuaicheDmpBindRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.ads.dsp.rtb.kuaiche.dmp.bind";
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
                                                        		                                    	                                                 	                        	                                                                                                                                                                                                                                                                                                               private $adGroupPrice;
                              public function setAdGroupPrice($adGroupPrice ){
                 $this->adGroupPrice=$adGroupPrice;
                 $this->apiParas["adGroupPrice"] = $adGroupPrice;
              }

              public function getAdGroupPrice(){
              	return $this->adGroupPrice;
              }
                                                                                                                                                                                                                                                                                                                                              private $crowdId;
                              public function setCrowdId($crowdId ){
                 $this->crowdId=$crowdId;
                 $this->apiParas["crowdId"] = $crowdId;
              }

              public function getCrowdId(){
              	return $this->crowdId;
              }
                                                                                                                                                                                                                                                                                                                                              private $isUsed;
                              public function setIsUsed($isUsed ){
                 $this->isUsed=$isUsed;
                 $this->apiParas["isUsed"] = $isUsed;
              }

              public function getIsUsed(){
              	return $this->isUsed;
              }
                                                                                                                                        	                   			private $adGroupId;
    	                        
	public function setAdGroupId($adGroupId){
		$this->adGroupId = $adGroupId;
         $this->apiParas["adGroupId"] = $adGroupId;
	}

	public function getAdGroupId(){
	  return $this->adGroupId;
	}

                                                                        		                                    	                        	                        	                        	                            }





        
 

