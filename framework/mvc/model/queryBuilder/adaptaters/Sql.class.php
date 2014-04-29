<?php

namespace framework\mvc\model\queryBuilder\adaptaters;

use framework\mvc\model\queryBuilder\IAdaptater;

class Sql implements IAdaptater {

    protected $_primaryClause = '';
    protected $_fromTable = '';
    protected $_fromTableAlias = null;
    protected $_whereClauses = array();

    public function __construct() {
        
    }

    public function getQuery($reset = true) {
        $query = $this->_primaryClause . ' ';
        $query .= 'FROM `' . $this->_fromTable . '` ';

        if (!empty($this->_whereClauses))
            $query .= 'WHERE ';
        $lastClauseType = '';
        foreach ($this->_whereClauses as &$clause) {
            $query .= (!empty($lastClauseType)) ? ' ' . $lastClauseType . ' ' : '';
            $query .= $this->_fromTable . '.`' . $clause['field'] . '` ' . $clause['equality'] . ' ' . $clause['value'];
            $lastClauseType = $clause['type'];
        }
        if ($reset)
            $this->resetQuery();
        
        return $query;
    }

    public function resetQuery() {
        $this->_primaryClause = '';
        $this->_fromClause = '';
        $this->_whereClauses = array();
    }

    public function select($columns = array()) {
        $this->_primaryClause = 'SELECT';
        return $this;
    }

    public function insert() {
        $this->_primaryClause = 'INSERT';
        return $this;
    }

    public function update() {
        $this->_primaryClause = 'UPDATE';
        return $this;
    }

    public function delete() {
        $this->_primaryClause = 'DELETE';
        return $this;
    }

    public function from($tableName, $tableAlias = null) {
        if (!is_string($tableName))
            throw new \Exception('Table name must be a string');
        if (!is_null($tableAlias) && !is_string($tableAlias))
            throw new \Exception('Table name must be a string');

        $this->_fromTable = $tableName;
        $this->_fromTableAlias = $tableAlias;
        return $this;
    }

    public function where($clauses) {
        if (!is_array())
            throw new \Exception('Where clauses must be an array');
        foreach ($clauses as &$clause) {
            if (!isset($clause['field']))
                throw new \Exception('Invalid clause, miss field');
            if (!isset($clause['value']))
                throw new \Exception('Invalid clause, miss value');
            $equality = isset($clause['equality']) ? $clause['equality'] : self::EQUALITY_EQUAL;
            $type = isset($clause['type']) ? $clause['type'] : $type = self::WHERE_AND;

            $this->addWhere($clause['field'], $clause['value'], $equality, $type);
        }
    }

    public function addWhere($field, $value, $equality = self::EQUALITY_EQUAL, $type = self::WHERE_AND) {
        if (!is_string($field))
            throw new \Exception('Field name must be a string');
        if ($equality != self::EQUALITY_EQUAL && $equality != self::EQUALITY_GT && $equality != self::EQUALITY_GTE && $equality != self::EQUALITY_LIKE && $equality != self::EQUALITY_LT && $equality != self::EQUALITY_LTE)
            throw new \Exception('Invalid equality');

        $this->_whereClauses[] = array(
            'field' => $field,
            'value' => $value,
            'equality' => $equality,
            'type' => $type
        );
    }

}

?>