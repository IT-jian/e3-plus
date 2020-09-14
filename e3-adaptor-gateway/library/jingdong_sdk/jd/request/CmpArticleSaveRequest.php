<?php
class CmpArticleSaveRequest
{
	private $apiParas = array();
	
	public function getApiMethodName(){
	  return "jingdong.cmp.article.save";
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
                                                        		                                    	                   			private $summary;
    	                        
	public function setSummary($summary){
		$this->summary = $summary;
         $this->apiParas["summary"] = $summary;
	}

	public function getSummary(){
	  return $this->summary;
	}

                        	                   			private $saveType;
    	                        
	public function setSaveType($saveType){
		$this->saveType = $saveType;
         $this->apiParas["saveType"] = $saveType;
	}

	public function getSaveType(){
	  return $this->saveType;
	}

                        	                   			private $publishTime;
    	                        
	public function setPublishTime($publishTime){
		$this->publishTime = $publishTime;
         $this->apiParas["publishTime"] = $publishTime;
	}

	public function getPublishTime(){
	  return $this->publishTime;
	}

                        	                        	                   			private $dpContent;
    	                        
	public function setDpContent($dpContent){
		$this->dpContent = $dpContent;
         $this->apiParas["dpContent"] = $dpContent;
	}

	public function getDpContent(){
	  return $this->dpContent;
	}

                        	                   			private $skuStr;
    	                        
	public function setSkuStr($skuStr){
		$this->skuStr = $skuStr;
         $this->apiParas["skuStr"] = $skuStr;
	}

	public function getSkuStr(){
	  return $this->skuStr;
	}

                        	                   			private $businessId;
    	                        
	public function setBusinessId($businessId){
		$this->businessId = $businessId;
         $this->apiParas["businessId"] = $businessId;
	}

	public function getBusinessId(){
	  return $this->businessId;
	}

                        	                   			private $threeDskuStr;
    	                        
	public function setThreeDskuStr($threeDskuStr){
		$this->threeDskuStr = $threeDskuStr;
         $this->apiParas["threeDskuStr"] = $threeDskuStr;
	}

	public function getThreeDskuStr(){
	  return $this->threeDskuStr;
	}

                        	                   			private $threeDimensionalImgInfo;
    	                        
	public function setThreeDimensionalImgInfo($threeDimensionalImgInfo){
		$this->threeDimensionalImgInfo = $threeDimensionalImgInfo;
         $this->apiParas["threeDimensionalImgInfo"] = $threeDimensionalImgInfo;
	}

	public function getThreeDimensionalImgInfo(){
	  return $this->threeDimensionalImgInfo;
	}

                        	                   			private $title;
    	                        
	public function setTitle($title){
		$this->title = $title;
         $this->apiParas["title"] = $title;
	}

	public function getTitle(){
	  return $this->title;
	}

                        	                   			private $titlePicUrl;
    	                        
	public function setTitlePicUrl($titlePicUrl){
		$this->titlePicUrl = $titlePicUrl;
         $this->apiParas["titlePicUrl"] = $titlePicUrl;
	}

	public function getTitlePicUrl(){
	  return $this->titlePicUrl;
	}

                        	                   			private $content;
    	                        
	public function setContent($content){
		$this->content = $content;
         $this->apiParas["content"] = $content;
	}

	public function getContent(){
	  return $this->content;
	}

                        	                   			private $support3d;
    	                        
	public function setSupport3d($support3d){
		$this->support3d = $support3d;
         $this->apiParas["support3d"] = $support3d;
	}

	public function getSupport3d(){
	  return $this->support3d;
	}

                        	                   			private $labelIds;
    	                        
	public function setLabelIds($labelIds){
		$this->labelIds = $labelIds;
         $this->apiParas["labelIds"] = $labelIds;
	}

	public function getLabelIds(){
	  return $this->labelIds;
	}

                        	                   			private $articleType;
    	                        
	public function setArticleType($articleType){
		$this->articleType = $articleType;
         $this->apiParas["articleType"] = $articleType;
	}

	public function getArticleType(){
	  return $this->articleType;
	}

                        	                   			private $id;
    	                        
	public function setId($id){
		$this->id = $id;
         $this->apiParas["id"] = $id;
	}

	public function getId(){
	  return $this->id;
	}

                        	                   			private $channelId;
    	                        
	public function setChannelId($channelId){
		$this->channelId = $channelId;
         $this->apiParas["channelId"] = $channelId;
	}

	public function getChannelId(){
	  return $this->channelId;
	}

                            }





        
 

