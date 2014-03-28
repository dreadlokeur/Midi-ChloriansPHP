<?php

namespace framework\mvc\model;

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

        return $this;
    }

    public function setAlias($alias) {
        $this->_alias = $alias;

        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function getAlias() {
        return $this->_alias;
    }

}

?>
