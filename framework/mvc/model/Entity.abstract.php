<?php

namespace framework\mvc\model;

use framework\mvc\Model;
use framework\mvc\model\Relation;
use framework\mvc\model\Column;
use framework\Logger;

abstract class Entity {

    protected $_name;
    protected $_repostery;
    protected $_isMapped = false;
    protected $_columns = array();
    protected $_relations = array();
    protected $_parentName;
    protected $_parentEntity;

    public function __get($name) {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method))
            return $this->$method();
        elseif (property_exists($this, $name))
            return $this->$name;
        else {
            $name = '_' . $name;
            if (property_exists($this, $name))
                return $this->$name;

            return null;
        }
    }

    public function __set($name, $value) {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method))
            $this->$method($value);
        else {
            //is in columns
            if ($this->existColumn($name) && !Column::isValidColumnValue($this->getColumn($name), $value))
                throw new \Exception('Invalid type for column : "' . $name . '"');

            if (property_exists($this, $name))
                $this->$name = $value;
            else {
                $name = '_' . $name;
                if (property_exists($this, $name))
                    $this->$name = $value;
            }
        }


        return $this;
    }

    public function setName($name) {
        $name = explode('\\', (string)$name);
        if (is_array($name))
            $name = end($name);
        $this->_name = strtolower((string) $name);
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function setParentName($parentName) {
        $this->_parentName = (string) $parentName;
    }

    public function setParentEntity(Entity $parent) {
        return $this->_parentEntity = $parent;
    }

    public function getParentName() {
        return $this->_parentName;
    }

    public function getParentEntity() {
        return $this->_parentEntity;
    }

    public function isParentEntity($parentName, Entity $parent) {
        if ($parentName !== $this->getParentName() || $parent !== $this->getParentEntity())
            return false;

        return true;
    }

    public function hydrate($datas = array()) {
        foreach ($datas as $key => $value)
            $this->$key = $value;

        return $this;
    }

    public function mapping($columns = true, $relations = true) {
        if ($this->isMapped())
            throw new \Exception('Entity : "' . $this->getName() . '" already mapped');
        $reflexionClass = new \ReflectionClass($this);
        $reposteryName = false;
        //map default entity datas (repostery, table, tableAlias)
        $doc = $reflexionClass->getDocComment();
        if (preg_match('/@entity/', $doc)) {
            $annotationKeys = Model::getAnnotationKeys($doc);
            foreach ($annotationKeys as $annotationKey) {
                if ($annotationKey['name'] != 'repository')
                    continue;
                $reposteryName = $annotationKey['value'];
            }
        }
        // no repostery defined in entity annotation, set manualy, by entity name
        if (!$reposteryName)
            $reposteryName = $this->getName();

        //set repostery
        $this->setRepostery(Model::getInstance()->getRepostery($reposteryName));
        if ($columns || $relations) {
            $properties = $reflexionClass->getProperties();
            if ($columns)
                $this->_mapColumns($properties);
            if ($relations)
                $this->_mapRelations($properties, $reflexionClass->name);
        }

        $this->_isMapped = true;
    }

    public function isMapped() {
        return $this->_isMapped;
    }

    public function setRepostery(Repostery $repostery) {
        $this->_repostery = $repostery;
    }

    public function getRepostery() {
        return $this->_repostery;
    }

    public function addColumn(Column $column, $forceReplace = false) {
        if ($this->existColumn($column->getName())) {
            if (!$forceReplace)
                throw new \Exception('Column : "' . $column->getName() . '" already defined');

            Logger::getInstance()->debug('Column : "' . $column->getName() . '" already defined, was overrided');
        }

        $this->_columns[$column->getName()] = $column;

        return $this;
    }

    public function resetColumns() {
        $this->_columns = array();

        return $this;
    }

    public function getColumns() {
        return $this->_columns;
    }

    public function getColumn($columnName) {
        if (!array_key_exists((string) $columnName, $this->_columns))
            throw new \Exception('Column : "' . $columnName . '" undefined on entity : "' . $this->getName() . '"');

        return $this->_columns[$columnName];
    }

    public function existColumn($columnName) {
        return array_key_exists((string) $columnName, $this->_columns);
    }

    public function addRelation(Relation $relation, $forceReplace = false) {
        if ($this->existRelation($relation->getName())) {
            if (!$forceReplace)
                throw new \Exception('Relation : "' . $relation->getName() . '" already defined');

            Logger::getInstance()->debug('Relation : "' . $relation->getName() . '" already defined, was overrided');
        }

        $this->_relations[$relation->getName()] = $relation;

        return $this;
    }

    public function resetRelations() {
        $this->_relations = array();

        return $this;
    }

    public function getRelations() {
        return $this->_relations;
    }

    public function getRelation($relationName) {
        if (!array_key_exists((string) $relationName, $this->_relations))
            throw new \Exception('Relation : "' . $relationName . '" undefined');

        return $this->_relations[$relationName];
    }

    public function existRelation($relationName) {
        return array_key_exists((string) $relationName, $this->_relations);
    }

    private function _mapColumns($properties) {
        foreach ($properties as &$property) {
            $doc = $property->getDocComment();
            if (preg_match('/@column/', $doc)) {
                //create instance of Column
                $column = new Column(Model::getProprietyCleanedName($property));
                // get column datas by annotation proprieties
                $annotationKeys = Model::getAnnotationKeys($doc);
                foreach ($annotationKeys as $annotationKey) {
                    //set column propriety value
                    $methodName = 'set' . ucfirst($annotationKey['name']);
                    if (!method_exists($column, $methodName))
                        throw new \Exception('Invalid column propriety : "' . $annotationKey['name'] . '" on entity : "' . $this->getName() . '"');
                    $column->$methodName($annotationKey['value']);
                }

                //add into columns list
                $this->addColumn($column);
            }
        }
    }

    private function _mapRelations($properties, $parentName) {
        foreach ($properties as &$property) {
            $doc = $property->getDocComment();
            if (preg_match('/@relation/', $doc)) {
                $relationProprieties = array();
                $annotationKeys = Model::getAnnotationKeys($doc);
                foreach ($annotationKeys as $annotationKey) {
                    //create entityTarget object
                    if ($annotationKey['name'] == 'entityTarget') {
                        if ($this->getParentName() == $annotationKey['value'])
                            $annotationKey['value'] = $this->getParentEntity();
                        else {
                            $annotationKey['value'] = Model::getInstance()->getEntity($annotationKey['value'], array(
                                'parentName' => $parentName,
                                'parentEntity' => $this)
                            );
                        }
                    }

                    // add into proprieties list
                    $relationProprieties[$annotationKey['name']] = $annotationKey['value'];
                }
                //check if proprieties defined
                if (!isset($relationProprieties['type']) || !isset($relationProprieties['entityTarget']) || !isset($relationProprieties['columnTarget']) || !isset($relationProprieties['columnParent']))
                    throw new \Exception('Relation annotation  : "' . Model::getProprietyCleanedName($property) . '" invalid');

                //create relation instance
                $relation = new Relation(Model::getProprietyCleanedName($property), $relationProprieties['type'], $relationProprieties['entityTarget'], $this, $relationProprieties['columnTarget'], $relationProprieties['columnParent']);
                //add into relations list
                $this->addRelation($relation);
            }
        }
    }

}

?>
