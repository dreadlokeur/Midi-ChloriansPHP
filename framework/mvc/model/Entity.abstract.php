<?php

namespace framework\mvc\model;

use framework\mvc\Model;
use framework\mvc\model\Relation;
use framework\mvc\model\Column;
use framework\utility\Tools;
use framework\utility\Validate;
use framework\Logger;

abstract class Entity {

    protected $_name = null;
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
                throw new \Exception('invalid type or  for column : "' . $name . '"');

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
        $this->_name = (string) $name;
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
        if ($columns)
            $this->loadColumns();
        if ($relations)
            $this->loadRelations();
    }

    public function loadColumns() {
        Logger::getInstance()->debug('Try load columns on entity : "' . $this->getName() . '"');
        $reflexionClass = new \ReflectionClass($this);
        $properties = $reflexionClass->getProperties();
        foreach ($properties as &$property) {
            $doc = $property->getDocComment();
            if (preg_match('/@column/', $doc)) {
                //create instance of Column
                $column = new Column($this->_getProprietyCleanedName($property));
                // get column datas by annotation proprieties
                $annotationKeys = $this->_getAnnotationKeys($doc);
                foreach ($annotationKeys as $annotationKey) {
                    //set column propriety value
                    $methodName = 'set' . ucfirst($annotationKey['name']);
                    if (!method_exists($column, $methodName))
                        throw new \Exception('Invalid column propriety : "' . $annotationKey['name'] . '" on entity : "' . $this->getName() . '"');
                    $column->$methodName($annotationKey['value']);
                }

                //add into columns list
                $this->addColumn($column);
                Logger::getInstance()->debug('Column : "' . $column->getName() . '" loaded on entity : "' . $this->getName() . '"');
            }
        }

        return $this;
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

    public function loadRelations() {
        Logger::getInstance()->debug('Try load relations on entity : "' . $this->getName() . '"');
        $reflexionClass = new \ReflectionClass($this);
        $properties = $reflexionClass->getProperties();
        foreach ($properties as &$property) {
            $doc = $property->getDocComment();
            if (preg_match('/@relation/', $doc)) {
                $relationProprieties = array();
                $annotationKeys = $this->_getAnnotationKeys($doc);
                foreach ($annotationKeys as $annotationKey) {
                    //create entityTarget object
                    if ($annotationKey['name'] == 'entityTarget') {
                        Logger::getInstance()->debug('Try load relation : "' . $annotationKey['value'] . '" on entity : "' . $this->getName() . '"');
                        if ($this->getParentName() == $annotationKey['value'])
                            $annotationKey['value'] = $this->getParentEntity();
                        else {
                            $annotationKey['value'] = Model::factoryEntity($annotationKey['value'], array(
                                        'parentName' => $reflexionClass->name,
                                        'parentEntity' => $this)
                            );
                        }
                    }

                    // add into proprieties list
                    $relationProprieties[$annotationKey['name']] = $annotationKey['value'];
                }
                //check if proprieties defined
                if (!isset($relationProprieties['type']) || !isset($relationProprieties['entityTarget']) || !isset($relationProprieties['columnTarget']) || !isset($relationProprieties['columnParent']))
                    throw new \Exception('Relation annotation  : "' . $this->_getProprietyCleanedName($property) . '" invalid');

                //create relation instance
                $relation = new Relation($this->_getProprietyCleanedName($property), $relationProprieties['type'], $relationProprieties['entityTarget'], $this, $relationProprieties['columnTarget'], $relationProprieties['columnParent']);
                //add into relations list
                $this->addRelation($relation);
                Logger::getInstance()->debug('Relation : "' . $relation->getName() . '" loaded on entity : "' . $this->getName() . '"');
            }
        }

        return $this;
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
        return $this->_columns;
    }

    public function getRelation($relationName) {
        if (!array_key_exists((string) $relationName, $this->_relations))
            throw new \Exception('Relation : "' . $relationName . '" undefined');

        return $this->_relations[$relationName];
    }

    public function existRelation($relationName) {
        return array_key_exists((string) $relationName, $this->_relations);
    }

    private function _getAnnotationKeys($annotation) {
        if (!is_string($annotation))
            throw new \Exception('Annotation must be a string');
        //clean
        $keys = explode(',', preg_replace(array('/\*/', '/\s+/', '/\(/', '/\)/'), '', Tools::selectStringByDelimiter($annotation, '(', ')')));
        $annotationKeys = array();
        foreach ($keys as &$key) {
            $keyDatas = explode('=', $key);
            if (!$keyDatas || (!is_array($keyDatas) && count($keyDatas < 2)))
                throw new \Exception('Invalid annotation : "' . $key . '"');
            //check key name
            if (!Validate::isVariableName($keyDatas[0]))
                throw new \Exception('Annotation key : "' . $keyDatas[0] . '" must be a valid variable name');

            $annotationKeys [] = array(
                'name' => $keyDatas[0],
                'value' => Tools::castValue(preg_replace(array('/\"/'), '', $keyDatas[1]))
            );
        }

        return $annotationKeys;
    }

    private function _getProprietyCleanedName(\ReflectionProperty $property) {
        return preg_replace(array('/\_/'), '', $property->getName());
    }

}
?>
