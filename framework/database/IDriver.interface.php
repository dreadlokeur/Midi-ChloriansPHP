<?php

namespace framework\database;

interface IDriver {

    public function __construct($configName);

    public function connection($serverType);

    public function disconnect();

    public function set($query);

    public function bind($parameter, $type, $key = false);

    public function execute();

    public function getLogs();

    public function isValidDriver($driver);

    public function fetch();

    public function fetchAll();
}

?>
