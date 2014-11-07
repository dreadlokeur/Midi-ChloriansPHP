<?php

namespace framework\security\adaptaters;

use framework\Logger;
use framework\Session;
use framework\mvc\Router;
use framework\network\Http;
use framework\utility\Validate;
use framework\security\IAdaptater;

class Csrf implements IAdaptater {

    protected $_name = null;
    protected $_autorun = false;
    protected $_errorRedirect = false;
    protected $_timeValidity = 0;
    protected $_urlsReferer = array();
    protected $_token = null;
    protected $_allowMultiple = true;
    protected $_sessionKeyTokenName = 'csrfToken';
    protected $_sessionKeyTokenTimeName = 'csrfTokenTime';
    protected $_tokenName = null;

    public function __construct($options = array()) {
        if (!isset($options['name']))
            throw new \Exception('Miss param name');
        $this->setName($options['name']);

        if (isset($options['autorun']))
            $this->setAutorun($options['autorun']);

        if (isset($options['timeValidity']))
            $this->setTimeValidity($options['timeValidity']);


        if (isset($options['urlsReferer']))
            $this->setUrlsReferer($options['urlsReferer']);

        if (isset($options['allowMultiple']))
            $this->setAllowMultiple($options['allowMultiple']);

        if (isset($options['name']))
            $this->setName($options['name']);

        if (isset($options['errorRedirect']))
            $this->setErrorRedirect($options['errorRedirect']);

        if (isset($options['sessionKeyTokenName']))
            $this->setSessionKeyTokenName($options['sessionKeyTokenName']);
        if (isset($options['sessionKeyTokenTimeName']))
            $this->setSessionKeyTokenTimeName($options['sessionKeyTokenTimeName']);

        $this->setTokenName(isset($options['tokenName']) ? $options['tokenName'] : $this->getName() );


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
        $controller = Router::getInstance()->getCurrentController();
        $this->create();
        // check
        if (Http::isPost() && !$this->check(Http::getPost($this->getTokenName()))) {
            //add error
            if ($controller)
                $controller->addError('Csrf invalid', 'csrf');
            if ($this->getErrorRedirect())
                Router::getInstance()->show403(true);
        }

        //assign token value
        if ($controller) {
            $controller->addAjaxDatas($this->getTokenName(), $this->get());
            $controller->tpl->setVar($this->getTokenName(), $this->get());
        }

        $this->set();
        Logger::getInstance()->debug('Security was run', 'security' . $this->getName());
    }

    public function stop() {
        $this->flush();
        Logger::getInstance()->debug('Security was stopped', 'security' . $this->getName());
    }

    public function create() {
        $this->_token = uniqid(rand(), true);
        Logger::getInstance()->debug('Create token value : "' . $this->_token . '"', 'security' . $this->getName());
    }

    public function set() {
        if (is_null($this->_token)) {
            Logger::getInstance()->debug('Trying set uncreated token', 'security' . $this->getName());
            return;
        }

        $token = array();
        if ($this->getAllowMultiple())
            $token = Session::getInstance()->get($this->getName() . $this->getSessionKeyTokenName(), array());

        $token[] = $this->_token;
        Session::getInstance()->add($this->getName() . $this->getSessionKeyTokenName(), $token, true, true);
        Logger::getInstance()->debug('Set token value : "' . $this->_token . '" into session', 'security' . $this->getName());
        if ($this->getTimeValidity() > 0) {
            $time = array();
            if ($this->getAllowMultiple())
                $time = Session::getInstance()->get($this->getName() . $this->getSessionKeyTokenTimeName(), array());

            $timeVal = time();
            $time[$this->_token] = $timeVal;
            Session::getInstance()->add($this->getName() . $this->getSessionKeyTokenTimeName(), $time, true, true);
            Logger::getInstance()->debug('Set token time value : "' . $timeVal . '" into session', 'security' . $this->getName());
        }
    }

    public function get() {
        Logger::getInstance()->debug('Get token value : "' . $this->_token . '"', 'security' . $this->getName());
        return $this->_token;
    }

    public function check($checkingValue, $flush = false) {
        if (is_null($this->_token))
            return false;
        $tokenRealValue = Session::getInstance()->get($this->getName() . $this->getSessionKeyTokenName(), array());
        $tokenTimeRealValue = Session::getInstance()->get($this->getName() . $this->getSessionKeyTokenTimeName(), array());
        if ($flush)
            $this->flush();

        if (empty($tokenRealValue)) {
            Logger::getInstance()->debug('Token miss"', 'security' . $this->getName());
            return false;
        }
        if ($this->getTimeValidity() > 0 && empty($tokenTimeRealValue)) {
            Logger::getInstance()->debug('TokenTime miss"', 'security' . $this->getName());
            return false;
        }
        $urls = $this->getUrlsReferer();
        if (!empty($urls)) {
            foreach ($urls as &$url) {
                if (stripos(Http::getServer('HTTP_REFERER'), Router::getUrl($url)) !== false || Http::getServer('HTTP_REFERER') == Router::getUrl($url)) {
                    $match = true;
                    break;
                }
            }
            if (!isset($match)) {
                Logger::getInstance()->debug('Url referer : "' . Http::getServer('HTTP_REFERER') . '" invalid', 'security' . $this->getName());
                return false;
            }
        }

        // check value
        if (!in_array($checkingValue, $tokenRealValue)) {
            Logger::getInstance()->debug('Token : "' . (string) $checkingValue . '" invalid', 'security' . $this->getName());
            return false;
        }

        //check time
        if ($this->getTimeValidity() > 0) {
            if (!array_key_exists($checkingValue, $tokenTimeRealValue) || $tokenTimeRealValue[$checkingValue] <= time() - $this->getTimeValidity()) {
                Logger::getInstance()->debug('TokenTime too old"', 'security' . $this->getName());
                return false;
            }
        }

        Logger::getInstance()->debug('Token : "' . (string) $checkingValue . '" valid', 'security' . $this->getName());
        return true;
    }

    public function flush() {
        Session::getInstance()->delete($this->getName() . $this->getSessionKeyTokenName(), true);
        if ($this->getTimeValidity() > 0)
            Session::getInstance()->delete($this->getName() . $this->getSessionKeyTokenTimeName(), true);

        $this->_token = null;
    }

    public function setTimeValidity($time) {
        if (!is_int($time) || $time < 0)
            throw new \Exception('timeValidity must be an positif integer');

        $this->_timeValidity = $time;
    }

    public function getTimeValidity() {
        return $this->_timeValidity;
    }

    public function setUrlsReferer($urlsReferer) {
        if (is_array($urlsReferer)) {
            foreach ($urlsReferer as &$url)
                $this->addUrlReferer($url);
        } else
            $this->addUrlReferer($url);
    }

    public function addUrlReferer($url) {
        if (!is_string($url))
            throw new \Exception('Url referer must be a string');

        $this->_urlsReferer[] = $url;
    }

    public function getUrlsReferer() {
        return $this->_urlsReferer;
    }

    public function setAllowMultiple($bool) {
        if (!is_bool($bool))
            throw new \Exception('AllowMultiple must be a boolean');

        $this->_allowMultiple = $bool;
    }

    public function getAllowMultiple() {
        return $this->_allowMultiple;
    }

    public function setErrorRedirect($errorRedirect) {
        if (!is_bool($errorRedirect))
            throw new \Exception('errorRedirect must be a boolean');

        $this->_errorRedirect = $errorRedirect;
    }

    public function getErrorRedirect() {
        return $this->_errorRedirect;
    }

    public function setSessionKeyTokenName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('session key tokenName must be a valid variable name');

        $this->_sessionKeyTokenName = $name;
    }

    public function getSessionKeyTokenName() {
        return $this->_sessionKeyTokenName;
    }

    public function setSessionKeyTokenTimeName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('session key tokenTimeName must be a valid variable');

        $this->_sessionKeyTokenTimeName = $name;
    }

    public function getSessionKeyTokenTimeName() {
        return $this->_sessionKeyTokenTimeName;
    }

    public function setTokenName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('tokenName must be a valid variable');

        $this->_tokenName = $name;
    }

    public function getTokenName() {
        return $this->_tokenName;
    }

}

?>