<?php

namespace framework\mvc;

use framework\Database;

abstract class Model {

    protected $_modelDBName = '';
    protected $_modelDBTable = '';

    const PARAM_NULL = 0;
    const PARAM_INT = 1;
    const PARAM_STR = 2;
    const PARAM_LOB = 3;
    const PARAM_STMT = 4;
    const PARAM_BOOL = 5;
    const PARAM_INPUT_OUTPUT = 6;
    // Find type
    const FIND_LIKE = 'LIKE';
    const FIND_EQUAL = '=';
    const FIND_LT = '<';
    const FIND_LTE = '<=';
    const FIND_GT = '>';
    const FIND_GTE = '>=';
    //orderby
    const ORDER_BY_DESC = 'DESC';
    const ORDER_BY_ASC = 'ASC';

    public static function factoryManager($name, $datas) {
        // Factory model
        if (!is_string($name))
            throw new \Exception('Model name must be a string');

        if (\class_exists('models\\' . ucfirst($name) . 'Manager'))
            $modelClass = 'models\\' . ucfirst($name) . 'Manager';
        else
            $modelClass = $name;

        $inst = new \ReflectionClass($modelClass);
        if (!in_array('framework\\mvc\\IModelManager', $inst->getInterfaceNames()))
            throw new \Exception('Model class must be implement framework\mvc\IModelManager');

        if (is_array($datas))
            return $inst->newInstanceArgs($datas);

        return $inst->newInstance($datas);
    }

    public static function factoryObject($name, $datas) {
        // Factory model
        if (!is_string($name))
            throw new \Exception('Model name must be a string');

        if (\class_exists('models\\' . ucfirst($name) . 'Object'))
            $modelClass = 'models\\' . ucfirst($name) . 'Object';
        else
            $modelClass = $name;

        $inst = new \ReflectionClass($modelClass);
        if (!in_array('framework\\mvc\\IModelObject', $inst->getInterfaceNames()))
            throw new \Exception('Model class must be implement framework\mvc\IModelObject');

        return $inst->newInstance($datas);
    }

    public static function isValidFindType($findType) {
        return ($findType == self::FIND_LIKE || $findType == self::FIND_EQUAL || $findType == self::FIND_LT || $findType == self::FIND_LTE || $findType == self::FIND_GT || $findType == self::FIND_GTE);
    }

    public static function existsColumn($name) {
        if (!is_string($name))
            throw new \Exception('Column name must be a string');

        $class = get_called_class();
        if (!array_key_exists($name, $class::$_columnsName))
            return false;


        return true;
    }

    public static function getColumnType($columnName) {
        if (!self::existsColumn($columnName))
            throw new \Exception('Invalid column name : "' . $columnName . '"');

        $class = get_called_class();
        return $class::$_columnsType[$columnName];
    }

    public static function getColumnName($columnName) {
        if (!self::existsColumn($columnName))
            throw new \Exception('Invalid column name : "' . $columnName . '"');

        $class = get_called_class();
        return $class::$_columnsName[$columnName];
    }

    public static function getColumnsName() {
        $class = get_called_class();
        return $class::$_columnsName;
    }

    public static function isValidParameter($name, $type, $val = null) {
        $class = get_called_class();

        if (!self::existsColumn($name))
            return false;
        elseif ($type != $class::$_columnsType[$name])
            return false;
        else {
            if (!is_null($val) && self::getValueParamType($val) != $class::$_columnsType[$name])
                return false;

            return true;
        }
    }

    public static function isValidType($columnName, $type) {
        if (!self::existsColumn($columnName))
            return false;

        if (!is_null($type) && self::getValueParamType($type) !== self::getColumnType($columnName))
            return false;

        return true;
    }

    public static function getValueParamType($val) {
        $typeVal = gettype($val);
        switch ($typeVal) {
            case 'boolean':
                return self::PARAM_BOOL;
                break;
            case 'integer':
            case 'double':
                return self::PARAM_INT;
                break;
            case 'string':
                return self::PARAM_STR;
                break;
                break;
            case 'NULL':
                return self::PARAM_NULL;
                break;
            default:
                return false;
                break;
        }
    }

    public function __construct($dbName, $dbTable) {
        $this->setModelDBName($dbName);
        $this->setModelDBTable($dbTable);
    }

    public function hydrate($datas = array()) {
        foreach ($datas as $key => $value)
            $this->$key = $value;
    }

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
        elseif (property_exists($this, $name))
            $this->$name = $value;
        else {
            $name = '_' . $name;
            if (property_exists($this, $name))
                $this->$name = $value;
        }

        return $this;
    }

    public function getDb($returnEngine = false) {
        return Database::getDatabase($this->_modelDBName, $returnEngine);
    }

    public function setModelDBName($dbName) {
        $this->_modelDBName = $dbName;
    }

    public function setModelDBTable($dbName) {
        $this->_modelDBTable = $dbName;
    }

    public function getModelDBName() {
        return $this->_modelDBName;
    }

    public function getModelDBTable() {
        return $this->_modelDBTable;
    }

    public function execute($query, $parameters = array(), $returnLastInsertId = false, $closeStatement = false) {
        $engine = $this->getDb(true);
        $engine->set($query);
        foreach ($parameters as $paramValue => $paramType)
            $engine->bind($paramValue, $paramType);

        $engine->execute($closeStatement);


        if ($returnLastInsertId)
            return $engine->lastInsertId();
    }

}

?>