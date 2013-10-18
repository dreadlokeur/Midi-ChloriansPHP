<?php

namespace framework\mvc;

interface IModelManager {

    public function __construct($dbName = '', $dbtable = '');

    public function setModelDBName($dbName);

    public function setModelDBTable($dbTable);

    public function getDB();

    public function execute($query, $parameters = array());
}

?>