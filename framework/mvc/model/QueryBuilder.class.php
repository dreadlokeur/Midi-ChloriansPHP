<?php

namespace framework\mvc\model;

use framework\mvc\model\IQueryBuilder;

class QueryBuilder {

    //equality
    const EQUALITY_LIKE = 1;
    const EQUALITY_EQUAL = 2;
    const EQUALITY_LT = 3;
    const EQUALITY_LTE = 4;
    const EQUALITY_GT = 5;
    const EQUALITY_GTE = 6;
    //conditions
    const CONDITION_AND = 1;
    const CONDITION_OR = 2;

    protected $_builder;

    public function __construct(IQueryBuilder $builder) {
        $this->setBuilder($builder);
    }

    public function setBuilder(IQueryBuilder $builder) {
        $this->_builder = $builder;
    }

    public function getBuilder() {
        return $this->_builder;
    }

}
?>


