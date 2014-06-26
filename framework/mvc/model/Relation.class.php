<?php

/**
 * @relation(
 *  type="oneToOne|manyToOne|manyToMany", (required)
 *  target="entity name (with namespace)", (required)
 *  columnLocal="self column name", (required)
 *  columnJoin="mapped column name", (required)
 * )
 */

namespace framework\mvc\model;

use framework\mvc\model\Entity;
use framework\utility\Validate;

class Relation {

    const TYPE_ONE_TO_ONE = 'oneToOne';
    const TYPE_ONE_TO_MANY = 'oneToMany';
    const TYPE_MANY_TO_ONE = 'manyToOne';
    const TYPE_MANY_TO_MANY = 'manyToMany';

    protected $_name = null;
    protected $_entityTarget = null;
    protected $_entityParent = null;
    protected $_type = null;
    protected $_columnTarget = null;
    protected $_columnParent = null;

    public function __construct($name, $type, Entity $entityTarget, Entity $entityParent, $columnTarget, $columnParent) {
        $this->setName($name);
        $this->setType($type);
        $this->setTarget($entityTarget, $columnTarget);
        $this->setParent($entityParent, $columnParent);
    }

    public function setName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Relation name : "' . $name . '" must be a valid variable name');
        $this->_name = $name;
    }

    public function setType($type) {
        if ($type != self::TYPE_ONE_TO_ONE && $type != self::TYPE_ONE_TO_MANY && $type != self::TYPE_MANY_TO_ONE && $type != self::TYPE_MANY_TO_MANY)
            throw new \Exception('Invalid relation type : "' . $type . '"');
        $this->_type = $type;
    }

    public function setTarget(Entity $entityTarget, $columnTarget) {
        $this->_entityTarget = $entityTarget;
        if (!$this->_entityTarget->existColumn($columnTarget))
            throw new \Exception('Invalid relation , columnTarget : "' . $columnTarget . '" don\'t exist on entity : "' . $this->_entityTarget->getName() . '"');

        $this->_columnTarget = $this->_entityTarget->getColumn($columnTarget);
    }

    public function setParent(Entity $entityParent, $columnParent) {
        $this->_entityParent = $entityParent;
        if (!$this->_entityParent->existColumn($columnParent))
            throw new \Exception('Invalid relation , columnParent : "' . $columnParent . '" don\'t exist on entity : "' . $this->_entityTarget->getName() . '"');

        $this->_columnParent = $this->_entityParent->getColumn($columnParent);
    }

    public function getName() {
        return $this->_name;
    }

    public function getEntityTarget() {
        return $this->_entityTarget;
    }

    public function getColumnTarget() {
        return $this->_columnTarget;
    }

    public function getEntityParent() {
        return $this->_entityParent;
    }

    public function getColumnParent() {
        return $this->_columnParent;
    }

}

?>