<?php

namespace MidiChloriansPHP\mvc\model;

class Table {

    protected $_name;
    protected $_alias;

    public function __construct($name, $alias = null) {
        $this->setName($name);
        if (!is_null($alias))
            $this->setAlias($alias);
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function setAlias($alias) {
        $this->_alias = $alias;
    }

    public function getName() {
        return $this->_name;
    }

    public function getAlias() {
        return $this->_alias;
    }

}

?>
