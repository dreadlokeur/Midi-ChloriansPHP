<?php

namespace framework\security\form;

use framework\security\IForm;
use framework\Session;
use framework\network\Http;
use framework\Logger;
use framework\Config;

class Csrf implements IForm {

    protected $_formName = '';
    protected $_timeValidity = 0;
    protected $_urlsReferer = array();
    protected $_token = null;

    public function __construct($options = array()) {
        if (isset($options['timeValidity']))
            $this->_timeValidity = (int) $options['timeValidity'];

        if (isset($options['urlReferer'])) {
            if (is_array($options['urlReferer'])) {
                foreach ($options['urlReferer'] as &$url)
                    $this->_urlsReferer[] = Config::getUrl($url);
            }
            else
                $this->_urlsReferer[] = Config::getUrl($options['urlReferer']);
        }
    }

    public function setFormName($name) {
        $this->_formName = $name;
    }

    public function getFormName() {
        return $this->_formName;
    }

    public function create() {
        $this->_token = uniqid(rand(), true);
    }

    public function set() {
        if (is_null($this->_token)) {
            Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" tryint set uncreated token', 'security');
            return;
        }

        $session = Session::getInstance();
        $session->add($this->getFormName() . 'CsrfToken', $this->_token, true, true);
        if ($this->_timeValidity > 0)
            $session->add($this->getFormName() . 'CsrfTokenTime', time(), true, true);
    }

    public function get() {
        return $this->_token;
    }

    public function check($checkingValue, $addAttempt = true) {
        if (is_null($this->_token))
            return false;
        $session = Session::getInstance();
        $tokenRealValue = $session->get($this->getFormName() . 'CsrfToken');
        if (is_null($tokenRealValue)) {
            if ($addAttempt)
                $this->addAttempt('Token miss');
            return false;
        }
        $tokenTimeRealValue = $session->get($this->getFormName() . 'CsrfTokenTime');
        if ($this->_timeValidity > 0 && is_null($tokenTimeRealValue)) {
            if ($addAttempt)
                $this->addAttempt('TokenTime miss');
            return false;
        }
        if (!empty($this->_urlsReferer)) {
            foreach ($this->_urlsReferer as &$url) {
                if (stripos(Http::getServer('HTTP_REFERER'), $url) !== false || Http::getServer('HTTP_REFERER') == $url) {
                    $match = true;
                    break;
                }
            }
            if (!isset($match)) {
                if ($addAttempt)
                    $this->addAttempt('Url referer : "' . Http::getServer('HTTP_REFERER') . '" invalid');
                return false;
            }
            //if (!in_array(Http::getServer('HTTP_REFERER'), $this->_urlsReferer)) {
            //}
        }
        if ($tokenRealValue != $checkingValue) {
            if ($addAttempt)
                $this->addAttempt('Token : "' . $checkingValue . '" invalid, need : "' . $tokenRealValue . '" value');
            return false;
        }
        if ($tokenTimeRealValue <= time() - $this->_timeValidity) {
            if ($addAttempt)
                $this->addAttempt('TokenTime too old');
            return false;
        }

        return true;
    }

    public function addAttempt($attemptInfo = '') {
        Logger::getInstance()->debug('Crsf : "' . $this->getFormName() . '" attempt, information : "' . $attemptInfo . '"', 'security');
    }

    public function flush() {
        $session = Session::getInstance();
        $session->delete($this->getFormName() . 'CsrfToken', true);
        if ($this->_timeValidity > 0)
            $session->delete($this->getFormName() . 'CsrfTokenTime', true);

        $this->_token = null;
    }

}

?>