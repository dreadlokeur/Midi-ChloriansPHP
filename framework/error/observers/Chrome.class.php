<?php

namespace framework\error\observers;

use framework\Cli;

class Chrome implements \SplObserver {

    protected $_chrome = false;

    public function __construct() {
        if (!Cli::isCli()) {
            $this->_chrome = \ChromePHP::getInstance();
        }
    }

    public function update(\SplSubject $subject, $isException = false) {
        if ($this->_chrome == false)
            return;
        if (!$isException) {
            $error = $subject->getError();
            $this->_chrome->error($error->type . ' : ' . $error->message . ' in ' . $error->file . ' on line ' . $error->line);
        } else {
            $exception = $subject->getException();
            $this->_chrome->error($exception->type . ' : "' . $exception->message . '" in ' . $exception->file . ' on line ' . $exception->line . ' with trace : ' . chr(10) . $exception->trace);
        }
    }

}

?>