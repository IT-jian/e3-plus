<?php
class CreateEmployeeRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.createEmployee";
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
                                                        		                                    	                        	                   			private $storeId;
    	                        
	public function setStoreId($storeId){
		$this->storeId = $storeId;
         $this->apiParas["storeId"] = $storeId;
	}

	public function getStoreId(){
	  return $this->storeId;
	}

                        	                   			private $name;
    	                        
	public function setName($name){
		$this->name = $name;
         $this->apiParas["name"] = $name;
	}

	public function getName(){
	  return $this->name;
	}

                        	                   			private $employeeId;
    	                        
	public function setEmployeeId($employeeId){
		$this->employeeId = $employeeId;
         $this->apiParas["employeeId"] = $employeeId;
	}

	public function getEmployeeId(){
	  return $this->employeeId;
	}

                        	                   			private $phone;
    	                        
	public function setPhone($phone){
		$this->phone = $phone;
         $this->apiParas["phone"] = $phone;
	}

	public function getPhone(){
	  return $this->phone;
	}

                        	                   			private $caccountId;
    	                        
	public function setCaccountId($caccountId){
		$this->caccountId = $caccountId;
         $this->apiParas["caccountId"] = $caccountId;
	}

	public function getCaccountId(){
	  return $this->caccountId;
	}

                        	                   			private $openId;
    	                        
	public function setOpenId($openId){
		$this->openId = $openId;
         $this->apiParas["openId"] = $openId;
	}

	public function getOpenId(){
	  return $this->openId;
	}

                        	                   			private $userName;
    	                        
	public function setUserName($userName){
		$this->userName = $userName;
         $this->apiParas["userName"] = $userName;
	}

	public function getUserName(){
	  return $this->userName;
	}

                        	                   			private $imitateIp;
    	                        
	public function setImitateIp($imitateIp){
		$this->imitateIp = $imitateIp;
         $this->apiParas["imitateIp"] = $imitateIp;
	}

	public function getImitateIp(){
	  return $this->imitateIp;
	}

                        	                   			private $brandId;
    	                        
	public function setBrandId($brandId){
		$this->brandId = $brandId;
         $this->apiParas["brandId"] = $brandId;
	}

	public function getBrandId(){
	  return $this->brandId;
	}

                        	                   			private $bizId;
    	                        
	public function setBizId($bizId){
		$this->bizId = $bizId;
         $this->apiParas["bizId"] = $bizId;
	}

	public function getBizId(){
	  return $this->bizId;
	}

                        	                   			private $sourceType;
    	                        
	public function setSourceType($sourceType){
		$this->sourceType = $sourceType;
         $this->apiParas["sourceType"] = $sourceType;
	}

	public function getSourceType(){
	  return $this->sourceType;
	}

                        	                   			private $employeeType;
    	                        
	public function setEmployeeType($employeeType){
		$this->employeeType = $employeeType;
         $this->apiParas["employeeType"] = $employeeType;
	}

	public function getEmployeeType(){
	  return $this->employeeType;
	}

                        	                   			private $venderEmployeeId;
    	                        
	public function setVenderEmployeeId($venderEmployeeId){
		$this->venderEmployeeId = $venderEmployeeId;
         $this->apiParas["venderEmployeeId"] = $venderEmployeeId;
	}

	public function getVenderEmployeeId(){
	  return $this->venderEmployeeId;
	}

                            }





        
 

