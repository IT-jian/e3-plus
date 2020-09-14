<?php
class UeOrderNewEvaluateRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.ue.order.new.evaluate";
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
                                                        		                                    	                   			private $beginDate;
    	                        
	public function setBeginDate($beginDate){
		$this->beginDate = $beginDate;
         $this->apiParas["beginDate"] = $beginDate;
	}

	public function getBeginDate(){
	  return $this->beginDate;
	}

                        	                   			private $venderCode;
    	                        
	public function setVenderCode($venderCode){
		$this->venderCode = $venderCode;
         $this->apiParas["venderCode"] = $venderCode;
	}

	public function getVenderCode(){
	  return $this->venderCode;
	}

                        	                   			private $endDate;
    	                        
	public function setEndDate($endDate){
		$this->endDate = $endDate;
         $this->apiParas["endDate"] = $endDate;
	}

	public function getEndDate(){
	  return $this->endDate;
	}

                        	                   			private $appid;
    	                        
	public function setAppid($appid){
		$this->appid = $appid;
         $this->apiParas["appid"] = $appid;
	}

	public function getAppid(){
	  return $this->appid;
	}

                        	                   			private $page;
    	                        
	public function setPage($page){
		$this->page = $page;
         $this->apiParas["page"] = $page;
	}

	public function getPage(){
	  return $this->page;
	}

                        	                   			private $serviceTypeId;
    	                        
	public function setServiceTypeId($serviceTypeId){
		$this->serviceTypeId = $serviceTypeId;
         $this->apiParas["serviceTypeId"] = $serviceTypeId;
	}

	public function getServiceTypeId(){
	  return $this->serviceTypeId;
	}

                                                                             	                        	                                                                                                                                                                                                                                                                                                               private $orderNos;
                              public function setOrderNos($orderNos ){
                 $this->orderNos=$orderNos;
                 $this->apiParas["orderNos"] = $orderNos;
              }

              public function getOrderNos(){
              	return $this->orderNos;
              }
                                                                                                                }





        
 

