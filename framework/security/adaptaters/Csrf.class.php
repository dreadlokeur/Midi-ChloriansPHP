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
    protected $_tokenName = 'csrfToken';
    protected $_tokenTimeName = 'csrfTokenTime';

    public function __construct($options = array()) {
        if (!isset($options['name']))
            throw new \Exception('Miss param name');
        $this->setName($options['name']);

        if (isset($options['autorun']))
            $this->setAutorun($options['autorun']);

        if (isset($options['timeValidity']))
            $this->_timeValidity = (int) $options['timeValidity'];

        if (isset($options['urlsReferer'])) {
            if (is_array($options['urlsReferer'])) {
                foreach ($options['urlsReferer'] as &$url)
                    $this->_urlsReferer[] = $url;
            } else
                $this->_urlsReferer[] = $options['urlsReferer'];
        }
        if (isset($options['allowMultiple']))
            $this->_allowMultiple = (bool) $options['allowMultiple'];

        if (isset($options['name']))
            $this->setName($options['name']);

        if (isset($options['errorRedirect']))
            $this->setErrorRedirect($options['errorRedirect']);

        if (isset($options['tokenName']))
            $this->setTokenName($options['tokenName']);
        if (isset($options['tokenTimeName']))
            $this->setTokenTimeName($options['tokenTimeName']);

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
        $controller = Router::getInstance()->getControllerInstance();
        if ($controller) {
            $this->create();
            // check
            if (Http::isPost() && !$this->check(Http::getPost($this->getName()))) {
                //add error
                $controller->addError('Csrf invalid', 'csrf');
                if ($this->getErrorRedirect())
                    Router::getInstance()->show403(true);
            }

            //assign token value
            $controller->addAjaxDatas($this->getName(), $this->get());
            $controller->tpl->setVar($this->getName(), $this->get());
            $this->set();
            Logger::getInstance()->debug('Security was run', 'security' . $this->getName());
        }
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
        if ($this->_allowMultiple)
            $token = Session::getInstance()->get($this->getName() . $this->getTokenName(), array());

        $token[$this->_token] = $this->_token;
        Session::getInstance()->add($this->getName() . $this->getTokenName(), $token, true, true);
        Logger::getInstance()->debug('Set token value : "' . $this->_token . '" into session', 'security' . $this->getName());
        if ($this->_timeValidity > 0) {
            $time = array();
            if ($this->_allowMultiple)
                $time = Session::getInstance()->get($this->getName() . $this->getTokenTimeName(), array());

            $timeVal = time();
            $time[$this->_token] = $timeVal;
            Session::getInstance()->add($this->getName() . $this->getTokenTimeName(), $time, true, true);
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
        $tokenRealValue = Session::getInstance()->get($this->getName() . $this->getTokenName(), array());
        $tokenTimeRealValue = Session::getInstance()->get($this->getName() . $this->getTokenTimeName(), array());
        if ($flush)
            $this->flush();

        if (empty($tokenRealValue)) {
            Logger::getInstance()->debug('Token miss"', 'security' . $this->getName());
            return false;
        }
        if ($this->_timeValidity > 0 && empty($tokenTimeRealValue)) {
            Logger::getInstance()->debug('TokenTime miss"', 'security' . $this->getName());
            return false;
        }
        if (!empty($this->_urlsReferer)) {
            foreach ($this->_urlsReferer as &$url) {
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
        if (!array_key_exists($checkingValue, $tokenRealValue)) {
            Logger::getInstance()->debug('Token : "' . (string) $checkingValue . '" invalid', 'security' . $this->getName());
            return false;
        }

        //check time
        if ($this->_timeValidity > 0) {
            if (!array_key_exists($checkingValue, $tokenTimeRealValue) || $tokenTimeRealValue[$checkingValue] <= time() - $this->_timeValidity) {
                Logger::getInstance()->debug('TokenTime too old"', 'security' . $this->getName());
                return false;
            }
        }

        Logger::getInstance()->debug('Token : "' . (string) $checkingValue . '" valid', 'security' . $this->getName());
        return true;
    }

    public function flush() {
        Session::getInstance()->delete($this->getName() . $this->getTokenName(), true);
        if ($this->_timeValidity > 0)
            Session::getInstance()->delete($this->getName() . $this->getTokenTimeName(), true);

        $this->_token = null;
    }

    public function setErrorRedirect($errorRedirect) {
        if (!is_bool($errorRedirect))
            throw new \Exception('errorRedirect must be a boolean');

        $this->_errorRedirect = $errorRedirect;
    }

    public function getErrorRedirect() {
        return $this->_errorRedirect;
    }

    public function setTokenName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('tokenName must be a valid variable name');

        $this->_tokenName = $name;
    }

    public function getTokenName() {
        return $this->_tokenName;
    }

    public function setTokenTimeName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('tokenTimeName must be a valid variable');

        $this->_tokenTimeName = $name;
    }

    public function getTokenTimeName() {
        return $this->_tokenTimeName;
    }

}

?>