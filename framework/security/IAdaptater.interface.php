<?php

namespace framework\security;

interface IAdaptater {

    public function __construct($options = array());

    public function setName($name);

    public function getName();

    public function run();

    public function stop();

    public function create();

    public function set();

    public function get();

    public function check($checkingValue, $flush = false);

    public function flush();
}

?>
