<?php

namespace framework\security;

interface IApi {

    public function __construct($options = array());

    public function run();

    public function stop();
}

?>
