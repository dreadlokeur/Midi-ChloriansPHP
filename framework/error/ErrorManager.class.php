<?php

namespace framework\error;

use framework\mvc\Router;
use framework\Application;

class ErrorManager implements \SplSubject {

    use \framework\pattern\Singleton;

    protected $_observers; //object SplObjectStorage
    protected $_error = false;
    protected $_initializedError = false;
    protected $_clearErrorAfterSending = true;
    protected $_catchFatal = true;

    protected function __construct() {
        $this->_observers = new \SplObjectStorage();
    }

    public function start($catchFatal = true, $displayErrors = true) {
        if (!is_bool($catchFatal))
            throw new \Exception('catchFatal parameter must be a boolean');
        if (!is_bool($displayErrors))
            throw new \Exception('displayErrors parameter must be a boolean');
        if ($catchFatal) {
            register_shutdown_function(array($this, 'fatalErrorHandler'));
            $this->_catchFatal = true;
        }
        ini_set('display_errors', (int) $displayErrors);
        set_error_handler(array($this, 'errorHandler'));

        return $this;
    }

    public function stop() {
        restore_error_handler();
    }

    public function attach(\SplObserver $observer) {
        if ($this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" is already attached');
        $this->_observers->attach($observer);
        return $this;
    }

    public function detach(\SplObserver $observer) {
        if (!$this->_observers->contains($observer))
            throw new \Exception('Observer "' . $observer . '" don\'t exists');
        $this->_observers->detach($observer);
        return $this;
    }

    public function notify() {
        // Erase buffer
        $buffer = ob_get_status();
        if (!empty($buffer))
            ob_end_clean();

        if ($this->_observers->count()) {
            foreach ($this->_observers as $observer)
                $observer->update($this);
        }
        // Clear error for avoid multiple call
        if ($this->_clearErrorAfterSending)
            unset($this->_error);

        // Show internal server error (500)
        if (!Application::getDebug())
            Router::getInstance()->show500(true);

        // Exit script
        exit();
    }

    public function errorHandler($code, $message, $file, $line) {
        $this->_setError($code, $message, $file, $line);
        $this->notify();
        // Do not execute the PHP error handler
        return true;
    }

    public function fatalErrorHandler() {
        if (error_get_last() !== null) {
            $lastError = error_get_last();
            if ($lastError['type'] === E_ERROR) {
                $this->_setError('E_FATAL', $lastError['message'], $lastError['file'], $lastError['line']);
                $this->notify();
                // Do not execute the PHP error handler
                return true;
            }
        }
    }

    public function getError() {
        return $this->_error;
    }

    public function setClearErrorAfterSending($bool) {
        if (!is_bool($bool))
            throw new \Exception('clearExceptionAfterSending parameter must be a boolean');
        $this->_clearErrorAfterSending = $bool;
        return $this;
    }

    protected function _setError($code, $message, $file, $line) {
        $error = new \stdClass();
        $error->code = $code;
        $error->type = $this->_getErrorType($code);
        $error->message = $message;
        $error->file = $file;
        $error->line = $line;

        $this->_error = $error;
        $this->_initializedError = true;
    }

    protected function _getErrorType($errCode) {
        switch ($errCode) {
            case 'E_FATAL':
                $type = 'Fatal erreur';
                break;
            case E_ERROR:
                $type = 'Erreur';
                break;
            case E_WARNING:
                $type = 'Alerte';
                break;
            case E_PARSE:
                $type = 'Erreur d\'analyse';
                break;
            case E_NOTICE:
                $type = 'Note';
                break;
            case E_CORE_ERROR:
                $type = 'Core Error';
                break;
            case E_CORE_WARNING:
                $type = 'Core Warning';
                break;
            case E_COMPILE_ERROR:
                $type = 'Compile Error';
                break;
            case E_COMPILE_WARNING:
                $type = 'Compile Warning';
                break;
            case E_USER_ERROR:
                $type = 'Erreur spécifique';
                break;
            case E_USER_WARNING:
                $type = 'Alerte spécifique';
                break;
            case E_USER_NOTICE:
                $type = 'Note spécifique';
                break;
            case E_STRICT:
                $type = 'Runtime Notice';
                break;
            case E_RECOVERABLE_ERROR:
                $type = 'Catchable Fatal Error';
                break;
            case E_DEPRECATED:
                $type = 'Deprecated error';
                break;
            case E_USER_DEPRECATED:
                $type = 'Deprecated error spécifique';
                break;
            default:
                $type = 'Type d\'erreur inconnue';
                break;
        }
        return $type;
    }

}

?>