<?php
class CreateSplitAccountsEntityRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.createSplitAccountsEntity";
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
                                                        		                                    	                   			private $pin;
    	                        
	public function setPin($pin){
		$this->pin = $pin;
         $this->apiParas["pin"] = $pin;
	}

	public function getPin(){
	  return $this->pin;
	}

                        	                        	                   			private $accountName;
    	                        
	public function setAccountName($accountName){
		$this->accountName = $accountName;
         $this->apiParas["accountName"] = $accountName;
	}

	public function getAccountName(){
	  return $this->accountName;
	}

                        	                   			private $walletName;
    	                        
	public function setWalletName($walletName){
		$this->walletName = $walletName;
         $this->apiParas["walletName"] = $walletName;
	}

	public function getWalletName(){
	  return $this->walletName;
	}

                        	                   			private $relStoreIds;
    	                        
	public function setRelStoreIds($relStoreIds){
		$this->relStoreIds = $relStoreIds;
         $this->apiParas["relStoreIds"] = $relStoreIds;
	}

	public function getRelStoreIds(){
	  return $this->relStoreIds;
	}

                        	                   			private $defaultSplitType;
    	                        
	public function setDefaultSplitType($defaultSplitType){
		$this->defaultSplitType = $defaultSplitType;
         $this->apiParas["defaultSplitType"] = $defaultSplitType;
	}

	public function getDefaultSplitType(){
	  return $this->defaultSplitType;
	}

                            }





        
 

