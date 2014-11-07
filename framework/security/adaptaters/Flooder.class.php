<?php

namespace framework\security\adaptaters;

use framework\Cache;
use framework\Logger;
use framework\utility\Validate;
use framework\security\IAdaptater;

class Flooder implements IAdaptater {

    protected $_name = null;
    protected $_autorun = false;
    protected $_maxAttempts = 10;
    protected $_errorRedirect = false;
    protected $_cache = null;

    public function __construct($options = array()) {
        if (!isset($options['name']))
            throw new \Exception('Miss param name');
        $this->setName($options['name']);

        if (isset($options['autorun']))
            $this->setAutorun($options['autorun']);

        if (isset($options['maxAttempts']))
            $this->setMaxAttempts($options['maxAttempts']);

        if (isset($options['errorRedirect']))
            $this->setErrorRedirect($options['errorRedirect']);

        if (isset($options['cache']))
            $this->setCache($options['cache']);

        Logger::getInstance()->addGroup('security' . $this->getName(), 'Security ' . $this->getName(), true, true);
    }

    public function setName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('name must be a valid variable name');

        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function setAutorun($autorun) {
        if (!is_bool($autorun))
            throw new \Exception('Autorun must be a boolean');

        $this->_autorun = $autorun;
    }

    public function getAutorun() {
        return $this->_autorun;
    }

    public function run() {
        Logger::getInstance()->debug('Security was run', 'security' . $this->getName());
    }

    public function stop() {
        $this->flush();
        Logger::getInstance()->debug('Security was stopped', 'security' . $this->getName());
    }

    public function create() {
        
    }

    public function set() {
        
    }

    public function get() {
        
    }

    public function check($checkingValue, $flush = false) {
        
    }

    public function flush() {
        
    }

    public function setMaxAttempts($maxAttempts) {
        if (!is_int($maxAttempts) || $maxAttempts < 0)
            throw new \Exception('maxAttempts must be an positif integer');

        $this->_maxAttempts = $maxAttempts;
    }

    public function getMaxAttempts() {
        return $this->_maxAttempts;
    }

    public function setErrorRedirect($errorRedirect) {
        if (!is_bool($errorRedirect))
            throw new \Exception('errorRedirect must be a boolean');

        $this->_errorRedirect = $errorRedirect;
    }

    public function getErrorRedirect() {
        return $this->_errorRedirect;
    }

    public function setCache($cacheName) {
        $cache = Cache::getCache($cacheName);
        if (!$cache)
            throw new \Exception('Cache : "' . $cacheName . '" is not a valid cache');

        $this->_cache = $cache;
    }

    public function getCache() {
        return $this->_cache;
    }

}

?>
