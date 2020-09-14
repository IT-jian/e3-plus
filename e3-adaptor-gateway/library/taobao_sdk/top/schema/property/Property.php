<?php 
namespace Top\schema\property;

class Property{
    protected $_key;
    protected $_value;
    
    public function __construct($key,$value){
        $this->_key = $key;
        $this->_value = $value;
    }
    
    public function getKey() {
        return $this->_key;
    }
    
    public function setKey($key) {
        $this->_key = $key;
    }
    
    public function getValue() {
        return $this->_value;
    }
    
    public function setValue($value) {
        $this->_value = $value;
    }
}
?>