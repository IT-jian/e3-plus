<?php
class EdiArcSendRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.edi.arc.send";
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
                                                        		                                    	                   			private $vendorName;
    	                        
	public function setVendorName($vendorName){
		$this->vendorName = $vendorName;
         $this->apiParas["vendorName"] = $vendorName;
	}

	public function getVendorName(){
	  return $this->vendorName;
	}

                        	                   			private $payableAccountId;
    	                        
	public function setPayableAccountId($payableAccountId){
		$this->payableAccountId = $payableAccountId;
         $this->apiParas["payableAccountId"] = $payableAccountId;
	}

	public function getPayableAccountId(){
	  return $this->payableAccountId;
	}

                        	                   			private $billType;
    	                        
	public function setBillType($billType){
		$this->billType = $billType;
         $this->apiParas["billType"] = $billType;
	}

	public function getBillType(){
	  return $this->billType;
	}

                        	                   			private $billNo;
    	                        
	public function setBillNo($billNo){
		$this->billNo = $billNo;
         $this->apiParas["billNo"] = $billNo;
	}

	public function getBillNo(){
	  return $this->billNo;
	}

                        	                   			private $poNo;
    	                        
	public function setPoNo($poNo){
		$this->poNo = $poNo;
         $this->apiParas["poNo"] = $poNo;
	}

	public function getPoNo(){
	  return $this->poNo;
	}

                        	                   			private $respond;
    	                        
	public function setRespond($respond){
		$this->respond = $respond;
         $this->apiParas["respond"] = $respond;
	}

	public function getRespond(){
	  return $this->respond;
	}

                        	                   			private $amount;
    	                        
	public function setAmount($amount){
		$this->amount = $amount;
         $this->apiParas["amount"] = $amount;
	}

	public function getAmount(){
	  return $this->amount;
	}

                        	                        	                   			private $vendorCode;
    	                        
	public function setVendorCode($vendorCode){
		$this->vendorCode = $vendorCode;
         $this->apiParas["vendorCode"] = $vendorCode;
	}

	public function getVendorCode(){
	  return $this->vendorCode;
	}

                            }





        
 

