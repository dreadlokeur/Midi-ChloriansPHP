<?php

namespace framework\mvc\model;

use framework\mvc\model\QueryBuilder;

interface IQueryBuilder {

    public function __construct();

    public function getQuery($reset = true);

    public function resetQuery();

    public function select($columns = array());

    public function insert();

    public function update();

    public function delete();

    public function from($tableName, $tableAlias = null);

    public function where($clauses);

    public function addWhere($field, $value, $equality = QueryBuilder::EQUALITY_EQUAL, $condition = QueryBuilder::CONDITION_AND);
}

?>
