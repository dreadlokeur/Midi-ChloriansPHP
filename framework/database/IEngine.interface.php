<?php

namespace framework\database;

use framework\Database;

interface IEngine {

    public function __construct($configName);

    public function isValidDriver($driver);

    public function connection($serverType);

    public function disconnect();

    public function haveStatement();

    public function set($query);

    public function bind($value, $type = Database::PARAM_STR, $key = false, $bindType = Database::BIND_TYPE_PARAM);

    public function execute($closeStatement = false);

    public function fetch($fetchStyle = Database::FETCH_BOTH, $cursorOrientation = Database::FETCH_ORI_NEXT, $offset = 0);

    public function fetchAll($fetchStyle = Database::FETCH_BOTH, $fetchArgument = false, $ctorArgs = false);

    public function lastInsertId();

    public function count();

    public function isReadQuery($query);
}

?>
