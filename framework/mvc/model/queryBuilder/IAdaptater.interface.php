<?php

namespace framework\mvc\model\queryBuilder;

interface IAdaptater {

    const EQUALITY_LIKE = 'LIKE';
    const EQUALITY_EQUAL = '=';
    const EQUALITY_LT = '<';
    const EQUALITY_LTE = '<=';
    const EQUALITY_GT = '>';
    const EQUALITY_GTE = '>=';
    const WHERE_AND = 'AND';
    const WHERE_OR = 'OR';

    public function __construct();

    public function getQuery($reset = true);

    public function resetQuery();

    public function select($columns = array());

    public function insert();

    public function update();

    public function delete();

    public function from($tableName, $tableAlias = null);

    public function where($clauses);

    public function addWhere($field, $value, $equality = self::EQUALITY_EQUAL, $type = self::WHERE_AND);
}

?>
