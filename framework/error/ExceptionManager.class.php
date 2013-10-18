<?php

// http://www.php.net/manual/fr/reserved.exceptions.php
// TODO rework attach methode and getObservers : inspire by logger code ...

namespace framework\error;

class ExceptionManager implements \SplSubject {

    use \framework\pattern\Singleton;

    protected $_observers; //object SplObjectStorage
    protected $_exception = false;
    protected $_initializedException = false;
    protected $_clearExceptionAfterSending = true;

    protected function __construct() {
        $this->_observers = new \SplObjectStorage();
    }

    protected function _setException($message, $file, $line, $trace, $type) {
        $exception = new \stdClass();
        $exception->message = nl2br($message);
        $exception->file = $file;
        $exception->line = $line;
        $exception->trace = nl2br($trace);
        $exception->type = $type;

        $this->_exception = $exception;
        $this->_initializedException = true;
    }

    public function start() {
        set_exception_handler(array($this, 'exceptionHandler'));
        return $this;
    }

    public function stop() {
        restore_exception_handler();
    }

    public function attach(\SplObserver $observer) {
        if ($this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" is already attached');
        $this->_observers->attach($observer);
        return $this;
    }

    public function detach(\SplObserver $observer) {
        if (!$this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" don\'t exist');
        $this->_observers->detach($observer);
        return $this;
    }

    public function notify() {
        if ($this->_observers->count()) {
            foreach ($this->_observers as $observer)
                $observer->update($this, true);
        }
        // Clear exception for avoid multiple call
        if ($this->_clearExceptionAfterSending)
            unset($this->_exception);

        // Exit
        exit();
    }

    public function exceptionHandler($ex) {
        $this->_setException($ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getTraceAsString(), 'Exception');
        $this->notify();
    }

    public function getException() {
        return $this->_exception;
    }

    public function setClearExceptionAfterSending($bool) {
        if (!is_bool($bool))
            throw new \Exception('clearExceptionAfterSending parameter must be a boolean');
        $this->_clearExceptionAfterSending = $bool;
        return $this;
    }

}

?>