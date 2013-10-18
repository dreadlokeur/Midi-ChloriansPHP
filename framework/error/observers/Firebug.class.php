<?php

namespace framework\error\observers;

use framework\Cli;

class Firebug implements \SplObserver {

    protected $_fb = false;

    public function __construct() {
        if (!Cli::isCli())
            $this->_fb = \FirePHP::getInstance(true);
    }

    public function update(\SplSubject $subject, $isException = false) {
        if ($this->_fb == false)
            return;
        if (!$isException) {
            $error = $subject->getError();
            $this->_fb->error($error->type . ' : ' . $error->message . ' in ' . $error->file . ' on line ' . $error->line);
        } else {
            $exception = $subject->getException();
            $this->_fb->critical($exception->type . ' : "' . $exception->message . '" in ' . $exception->file . ' on line ' . $exception->line . ' with trace : ' . chr(10) . $exception->trace);
        }
    }

}

?>