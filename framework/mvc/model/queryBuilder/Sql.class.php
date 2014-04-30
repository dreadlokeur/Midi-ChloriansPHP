<?php

namespace framework\mvc\model\queryBuilder;

use framework\mvc\model\IQueryBuilder;
use framework\mvc\model\QueryBuilder;

class Sql implements IQueryBuilder {

    protected $_equality = array(
        QueryBuilder::EQUALITY_LIKE => 'LIKE',
        QueryBuilder::EQUALITY_EQUAL => '=',
        QueryBuilder::EQUALITY_LT => '<',
        QueryBuilder::EQUALITY_LTE => '<=',
        QueryBuilder::EQUALITY_GT => '>',
        QueryBuilder::EQUALITY_GTE => '>='
    );
    protected $_conditions = array(
        QueryBuilder::CONDITION_AND => 'AND',
        QueryBuilder::CONDITION_OR => 'OR'
    );
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
        $lastClauseCondition = '';
        foreach ($this->_whereClauses as &$clause) {
            $query .= (!empty($lastClauseCondition)) ? ' ' . $lastClauseCondition . ' ' : '';
            $query .= $this->_fromTable . '.`' . $clause['field'] . '` ' . $this->_equality[$clause['equality']] . ' ' . $clause['value'];
            $lastClauseCondition = $this->_conditions[$clause['condition']];
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
            $equality = isset($clause['equality']) ? $clause['equality'] : QueryBuilder::EQUALITY_EQUAL;
            $condition = isset($clause['condition']) ? $clause['condition'] : $condition = QueryBuilder::CONDITION_AND;

            $this->addWhere($clause['field'], $clause['value'], $equality, $condition);
        }
    }

    public function addWhere($field, $value, $equality = QueryBuilder::EQUALITY_EQUAL, $condition = QueryBuilder::CONDITION_AND) {
        if (!is_string($field))
            throw new \Exception('Field name must be a string');

        if (!is_string($equality) && !is_int($equality))
            throw new \Exception('Equality must be an integer or a string');
        if (!array_key_exists($equality, $this->_equality))
            throw new \Exception('Invalid equality');

        if (!is_string($condition) && !is_int($condition))
            throw new \Exception('Condition must be an integer or a string');
        if (!array_key_exists($condition, $this->_conditions))
            throw new \Exception('Invalid condition');

        $this->_whereClauses[] = array(
            'field' => $field,
            'value' => $value,
            'equality' => $equality,
            'condition' => $condition
        );
    }

}

?>