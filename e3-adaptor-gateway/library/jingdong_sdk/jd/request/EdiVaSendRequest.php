<?php
class EdiVaSendRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.edi.va.send";
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

                        	                   			private $billNo;
    	                        
	public function setBillNo($billNo){
		$this->billNo = $billNo;
         $this->apiParas["billNo"] = $billNo;
	}

	public function getBillNo(){
	  return $this->billNo;
	}

                        	                   			private $businessType;
    	                        
	public function setBusinessType($businessType){
		$this->businessType = $businessType;
         $this->apiParas["businessType"] = $businessType;
	}

	public function getBusinessType(){
	  return $this->businessType;
	}

                        	                   			private $totalAmount;
    	                        
	public function setTotalAmount($totalAmount){
		$this->totalAmount = $totalAmount;
         $this->apiParas["totalAmount"] = $totalAmount;
	}

	public function getTotalAmount(){
	  return $this->totalAmount;
	}

                        	                        	                   			private $vendorCode;
    	                        
	public function setVendorCode($vendorCode){
		$this->vendorCode = $vendorCode;
         $this->apiParas["vendorCode"] = $vendorCode;
	}

	public function getVendorCode(){
	  return $this->vendorCode;
	}

                                                                             	                        	                                                                                                                                                                                                                                                                                                               private $settleNo;
                              public function setSettleNo($settleNo ){
                 $this->settleNo=$settleNo;
                 $this->apiParas["settleNo"] = $settleNo;
              }

              public function getSettleNo(){
              	return $this->settleNo;
              }
                                                                                                                                                                                                                                                                                                                                              private $payableAccountId;
                              public function setPayableAccountId($payableAccountId ){
                 $this->payableAccountId=$payableAccountId;
                 $this->apiParas["payableAccountId"] = $payableAccountId;
              }

              public function getPayableAccountId(){
              	return $this->payableAccountId;
              }
                                                                                                                                                                                                                                                                                                                                              private $billType;
                              public function setBillType($billType ){
                 $this->billType=$billType;
                 $this->apiParas["billType"] = $billType;
              }

              public function getBillType(){
              	return $this->billType;
              }
                                                                                                                                                                                                                                                                                                                                              private $verificationBillNo;
                              public function setVerificationBillNo($verificationBillNo ){
                 $this->verificationBillNo=$verificationBillNo;
                 $this->apiParas["verificationBillNo"] = $verificationBillNo;
              }

              public function getVerificationBillNo(){
              	return $this->verificationBillNo;
              }
                                                                                                                                                                                                                                                                                                                                              private $billDate;
                              public function setBillDate($billDate ){
                 $this->billDate=$billDate;
                 $this->apiParas["billDate"] = $billDate;
              }

              public function getBillDate(){
              	return $this->billDate;
              }
                                                                                                                                                                                                                                                                                                                                              private $poNo;
                              public function setPoNo($poNo ){
                 $this->poNo=$poNo;
                 $this->apiParas["poNo"] = $poNo;
              }

              public function getPoNo(){
              	return $this->poNo;
              }
                                                                                                                                                                                                                                                                                                                                              private $soNo;
                              public function setSoNo($soNo ){
                 $this->soNo=$soNo;
                 $this->apiParas["soNo"] = $soNo;
              }

              public function getSoNo(){
              	return $this->soNo;
              }
                                                                                                                                                                                                                                                                                                                                              private $amount;
                              public function setAmount($amount ){
                 $this->amount=$amount;
                 $this->apiParas["amount"] = $amount;
              }

              public function getAmount(){
              	return $this->amount;
              }
                                                                                                                                                                                                                                                                                                                                              private $memo;
                              public function setMemo($memo ){
                 $this->memo=$memo;
                 $this->apiParas["memo"] = $memo;
              }

              public function getMemo(){
              	return $this->memo;
              }
                                                                                                                                                                 	                        	                                                                                                                                                                                                                                                                                                               private $invoiceNo;
                              public function setInvoiceNo($invoiceNo ){
                 $this->invoiceNo=$invoiceNo;
                 $this->apiParas["invoiceNo"] = $invoiceNo;
              }

              public function getInvoiceNo(){
              	return $this->invoiceNo;
              }
                                                                                                                                                                                                                                                                                                                                              private $invoiceCode;
                              public function setInvoiceCode($invoiceCode ){
                 $this->invoiceCode=$invoiceCode;
                 $this->apiParas["invoiceCode"] = $invoiceCode;
              }

              public function getInvoiceCode(){
              	return $this->invoiceCode;
              }
                                                                                                                                                                                                                                                                                                                                              private $taxAmount;
                              public function setTaxAmount($taxAmount ){
                 $this->taxAmount=$taxAmount;
                 $this->apiParas["taxAmount"] = $taxAmount;
              }

              public function getTaxAmount(){
              	return $this->taxAmount;
              }
                                                                                                                }





        
 

