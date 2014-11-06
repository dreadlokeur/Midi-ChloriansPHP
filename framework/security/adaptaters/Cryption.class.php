<?php

//TODO must be completed

namespace framework\security\adaptaters;

use framework\security\IAdaptater;
use framework\utility\Validate;

class Cryption implements IAdaptater {

    protected $_name = null;
    protected $_autorun = false;

    public function __construct($options = array()) {
        throw new \Exception('Not yet');
    }

    public function setName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('name must be a valid variable name');

        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function setAutorun($autorun) {
        if (!is_bool($autorun))
            throw new \Exception('Autorun must be a boolean');

        $this->_autorun = $autorun;
    }

    public function getAutorun() {
        return $this->_autorun;
    }

    public function run() {
        
    }

    public function stop() {
        
    }

    public function create() {
        
    }

    public function set() {
        
    }

    public function get() {
        
    }

    public function check($checkingValue, $flush = false) {
        
    }

    public function flush() {
        
    }

    public function cryptPassword($password, $algorithm, $depth) {
        
    }

    public function checkPassword($cryptedPassword, $passwordCheck, $algorithm, $string, $depth) {
        
    }

    public function getPasswordInfo($cryptedPassword) {
        
    }

}

?>
