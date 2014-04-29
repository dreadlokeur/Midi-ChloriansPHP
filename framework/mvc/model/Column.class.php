<?php

/**
 * @column(
 *  type="boolean|integer|smallint|bigint|string|text|datetime|datetimetz|date|time|decimal|float", (optional : default = string)
 *  length="size" (optional, only for string)
 *  unique="true|false",  (optional : default = false)
 *  notNull="true|false", (optional : default = false)
 *  default="a default value", (optional)
 *  primary="true|false" (optional : default = false)
 *  foreign="true|false", (optional : default = false)
 *  name="a name value", (optional)
 *  alias="a alias value", (optional)
 *  autoIncrement="true|false" (optional : default = false)
 * )
 */

namespace framework\mvc\model;

use framework\utility\Validate;

class Column {

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_BIGINT = 'bigint';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATETIMETZ = 'datetimetz';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_FLOAT = 'float';

    protected $_name;
    protected $_alias;
    protected $_default;
    protected $_type = self::TYPE_STRING;
    protected $_length = 255;
    protected $_unique = false;
    protected $_notNull = false;
    protected $_primary = false;
    protected $_foreign = false;
    protected $_required = false;
    protected $_autoIncrement = false;

    public static function isValidColumnValue(Column $column, $value) {
        //check type
        $valueType = gettype($value);
        switch ($valueType) {
            case 'boolean':
                if ($column->getType() != self::TYPE_BOOLEAN)
                    return false;
            case 'integer':
                if ($column->getType() != self::TYPE_INTEGER && $column->getType() != self::TYPE_SMALLINT && $column->getType() != self::TYPE_BIGINT)
                    return false;
            case 'double':
                if ($column->getType() != self::TYPE_DECIMAL && $column->getType() != self::TYPE_FLOAT)
                    return false;
            case 'string':
                //todo check date format...
                if ($column->getType() != self::TYPE_STRING && $column->getType() != self::TYPE_TEXT && $column->getType() != self::TYPE_DATETIME && $column->getType() != self::TYPE_DATETIMETZ && $column->getType() != self::TYPE_DATE && $column->getType() != self::TYPE_TIME)
                    return false;
                break;
            default:
                return false;
        }

        //check lenght and validity of value
        switch ($column->getType()) {
            case self::TYPE_BOOLEAN:
                if (!Validate::isBool($value))
                    return false;
                break;

            case self::TYPE_INTEGER:
                if (!Validate::isInt($value, 2147483647))
                    return false;
                break;
            case self::TYPE_SMALLINT:
                if (!Validate::isInt($value, 32767))
                    return false;
                break;
            case self::TYPE_BIGINT:
                if (!Validate::isInt($value, 9223372036854775807))
                    return false;
                break;
            case self::TYPE_STRING:
                if (!Validate::isString($value, false, $column->getLength()))
                    return false;
                break;
            case self::TYPE_TEXT:
                if (!Validate::isString($value, 255, false))
                    return false;
                break;
            case self::TYPE_DECIMAL:
            case self::TYPE_FLOAT:
                if (!Validate::isFloat($value))
                    return false;
                break;
            // TODO dates ...
            default:
                break;
        }

        return true;
    }

    public function __construct($name) {
        $this->setName($name);
    }

    public function setName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Column name : "' . $name . '" must be a valid variable name');
        $this->_name = $name;
    }

    public function setAlias($alias) {
        if (!Validate::isVariableName($alias))
            throw new \Exception('Column alias : "' . $alias . '" must be a valid variable name');
        $this->_alias = $alias;
    }

    public function setDefault($default) {
        $this->_default = $default;
    }

    public function setType($type) {
        switch ($type) {
            case self::TYPE_BOOLEAN:
            case self::TYPE_INTEGER:
            case self::TYPE_SMALLINT:
            case self::TYPE_BIGINT:
            case self::TYPE_STRING:
            case self::TYPE_TEXT:
            case self::TYPE_DECIMAL:
            case self::TYPE_FLOAT:
            case self::TYPE_DATETIME:
            case self::TYPE_DATETIMETZ:
            case self::TYPE_DATE:
            case self::TYPE_TIME:
                break;
            default:
                throw new \Exception('Column type invalid');
        }
        $this->_type = $type;
    }

    public function setLength($length) {
        //todo check by type...
        if (!is_int($length))
            throw new \Exception('Column length must be an integer');
        $this->_length = $length;
    }

    public function setUnique($unique) {
        if (!is_bool($unique))
            throw new \Exception('Column unique must be a boolean');
        $this->_unique = $unique;
    }

    public function setNotNull($notNull) {
        if (!is_bool($notNull))
            throw new \Exception('Column notNull must be a boolean');
        $this->_notNull = $notNull;
    }

    public function setPrimary($primary) {
        if (!is_bool($primary))
            throw new \Exception('Column primary must be a boolean');
        $this->_primary = $primary;
    }

    public function setForeign($foreign) {
        if (!is_bool($foreign))
            throw new \Exception('Column foreign must be a boolean');
        $this->_foreign = $foreign;
    }

    public function setAutoIncrement($autoIncrement) {
        if (!is_bool($autoIncrement))
            throw new \Exception('Column autoIncrement must be a boolean');
        $this->_autoIncrement = $autoIncrement;
    }

    public function getName() {
        return $this->_name;
    }

    public function getAlias() {
        return $this->_alias;
    }

    public function getDefault() {
        return $this->_default;
    }

    public function getType() {
        return $this->_type;
    }

    public function getLength() {
        return $this->_length;
    }

    public function isUnique() {
        return $this->_unique;
    }

    public function isForeign() {
        return $this->_foreign;
    }

    public function isNotNull() {
        return $this->_notNull;
    }

    public function isPrimary() {
        return $this->_primary;
    }

    public function isAutoIncrement() {
        return $this->_autoIncrement;
    }

}

?>