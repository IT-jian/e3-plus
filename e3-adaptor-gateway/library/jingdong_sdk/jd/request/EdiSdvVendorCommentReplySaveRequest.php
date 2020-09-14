<?php
class EdiSdvVendorCommentReplySaveRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.edi.sdv.vendor.comment.reply.save";
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
                                                        		                                    	                   			private $username;
    	                        
	public function setUsername($username){
		$this->username = $username;
         $this->apiParas["username"] = $username;
	}

	public function getUsername(){
	  return $this->username;
	}

                        	                   			private $commentId;
    	                        
	public function setCommentId($commentId){
		$this->commentId = $commentId;
         $this->apiParas["commentId"] = $commentId;
	}

	public function getCommentId(){
	  return $this->commentId;
	}

                        	                   			private $type;
    	                        
	public function setType($type){
		$this->type = $type;
         $this->apiParas["type"] = $type;
	}

	public function getType(){
	  return $this->type;
	}

                        	                   			private $parentReplyId;
    	                        
	public function setParentReplyId($parentReplyId){
		$this->parentReplyId = $parentReplyId;
         $this->apiParas["parentReplyId"] = $parentReplyId;
	}

	public function getParentReplyId(){
	  return $this->parentReplyId;
	}

                        	                   			private $targetReplyId;
    	                        
	public function setTargetReplyId($targetReplyId){
		$this->targetReplyId = $targetReplyId;
         $this->apiParas["targetReplyId"] = $targetReplyId;
	}

	public function getTargetReplyId(){
	  return $this->targetReplyId;
	}

                        	                   			private $content;
    	                        
	public function setContent($content){
		$this->content = $content;
         $this->apiParas["content"] = $content;
	}

	public function getContent(){
	  return $this->content;
	}

                        	                   			private $venderId;
    	                        
	public function setVenderId($venderId){
		$this->venderId = $venderId;
         $this->apiParas["venderId"] = $venderId;
	}

	public function getVenderId(){
	  return $this->venderId;
	}

                        	                        	                   			private $ip;
    	                        
	public function setIp($ip){
		$this->ip = $ip;
         $this->apiParas["ip"] = $ip;
	}

	public function getIp(){
	  return $this->ip;
	}

                        	                   			private $clientType;
    	                        
	public function setClientType($clientType){
		$this->clientType = $clientType;
         $this->apiParas["clientType"] = $clientType;
	}

	public function getClientType(){
	  return $this->clientType;
	}

                        	                   			private $uuid;
    	                        
	public function setUuid($uuid){
		$this->uuid = $uuid;
         $this->apiParas["uuid"] = $uuid;
	}

	public function getUuid(){
	  return $this->uuid;
	}

                            }





        
 

