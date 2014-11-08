<?php

namespace framework\security\adaptaters;

use framework\Cache;
use framework\Logger;
use framework\network\Http;
use framework\network\http\Method;
use framework\utility\Tools;
use framework\utility\Validate;
use framework\security\IAdaptater;

class Flooder implements IAdaptater {

    protected $_name = null;
    protected $_autorun = false;
    protected $_maxAttempts = 10;
    protected $_banTime = 600; // in second
    protected $_errorRedirect = false;
    protected $_cache = null;
    protected $_cacheKeyName = 'flooder';
    protected $_ip = null;
    protected $_httpMethods = array(Method::POST, Method::GET);

    public function __construct($options = array()) {
        if (!isset($options['name']))
            throw new \Exception('Miss param name');
        $this->setName($options['name']);

        if (!isset($options['cache']))
            throw new \Exception('Miss param cache name');
        $this->setCache($options['cache']);

        if (isset($options['autorun']))
            $this->setAutorun($options['autorun']);

        if (isset($options['maxAttempts']))
            $this->setMaxAttempts($options['maxAttempts']);

        if (isset($options['errorRedirect']))
            $this->setErrorRedirect($options['errorRedirect']);



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
        $this->create();
        $httpMethod = Http::getMethod();
        foreach ($this->_httpMethods as $method) {
            if ($method == $httpMethod) {
                $cache = $this->get();
                if (!is_null($cache)) {
                    $isBanned = $cache['isBanned'];
                    if ($isBanned > $this->_banTime) {
                        if ($this->getErrorRedirect())
                            Router::getInstance()->show403(true);
                        break;
                    } else {
                        $this->check($isBanned);
                    }
                }
            }
        }


        Logger::getInstance()->debug('Security was run', 'security' . $this->getName());
    }

    public function stop() {
        $this->flush();
        Logger::getInstance()->debug('Security was stopped', 'security' . $this->getName());
    }

    public function create() {
        $this->_ip = Tools::getUserIp();
        Logger::getInstance()->debug('ip : "' . $this->_ip . '"', 'security' . $this->getName());
    }

    public function set() {
        
    }

    public function get() {
        return $this->getCache()->read(md5($this->_ip . $this->getCacheKeyName()));
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

    public function getCacheKeyName() {
        return $this->_cacheKeyName;
    }

}

?>
