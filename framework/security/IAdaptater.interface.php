<?php

namespace framework\security;

interface IAdaptater {

    public function __construct($options = array());

    public function setName($name);

    public function getName();

    public function setAutorun($autorun);

    public function getAutorun();

    public function run();

    public function create();

    public function set();

    public function get();

    public function check($checkingValue, $flush = false);

    public function flush();
}

?>
