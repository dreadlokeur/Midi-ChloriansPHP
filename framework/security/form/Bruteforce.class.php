<?php

//TODO must be completed

namespace framework\security\form;

use framework\security\IForm;

class Bruteforce implements IForm {

    protected $_formName = '';

    public function __construct($options = array()) {
        
    }

    public function setFormName($name) {
        $this->_formName = $name;
    }

    public function getFormName() {
        return $this->_formName;
    }

    public function set() {
        
    }

    public function create() {
        
    }

    public function get() {
        
    }

    public function flush() {
        
    }

    public function check($checkingValue, $addAttempt = true) {
        
    }

    public function addAttempt($attemptInfo = '') {
        
    }

}

?>
