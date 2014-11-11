<?php

namespace MidiChloriansPHP\config;

use MidiChloriansPHP\config\Reader;

abstract class Loader {

    abstract public function load(Reader $reader);
}

?>
