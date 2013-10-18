<?php

namespace framework\error\observers;

use framework\Logger;

class Log implements \SplObserver {

    public function __construct() {
        
    }

    public function update(\SplSubject $subject, $isException = false) {
        if (!$isException) {
            $error = $subject->getError();
            $typeLog = ($error->code == 'E_FATAL') ? 'fatal' : 'error';
            Logger::getInstance()->$typeLog($error->type . ' : ' . $error->message . ' in ' . $error->file . ' on line ' . $error->line);
        } else {
            $exception = $subject->getException();
            Logger::getInstance()->critical($exception->type . ' : "' . $exception->message . '" in ' . $exception->file . ' on line ' . $exception->line . ' with trace : ' . chr(10) . $exception->trace);
        }
    }

}

?>