<?php

namespace MidiChloriansPHP\mvc\model;

abstract class QueryBuilder {

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

}
?>


