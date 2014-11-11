<?php

namespace MidiChloriansPHP\error\observers;

use MidiChloriansPHP\mvc\Router;

class Display implements \SplObserver {

    public function __construct() {
        
    }

    public function update(\SplSubject $subject, $isException = false) {
        Router::getInstance()->showDebugger($isException);
    }

}

?>
