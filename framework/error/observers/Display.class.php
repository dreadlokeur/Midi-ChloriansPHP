<?php

namespace framework\error\observers;

use framework\mvc\Dispatcher;

class Display implements \SplObserver {

    public function __construct() {
        
    }

    public function update(\SplSubject $subject, $isException = false) {
        Dispatcher::getInstance(PATH_CONTROLLERS)->showDebugger(false, $isException);
    }

}

?>