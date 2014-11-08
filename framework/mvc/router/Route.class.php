<?php

namespace framework\mvc\router;

use framework\utility\Validate;
use framework\network\http\Method;
use framework\network\http\ResponseCode;
use framework\network\http\Protocol;

class Route {

    protected $_name;
    protected $_controller;
    protected $_regex = true;
    protected $_requireSsl = false;
    protected $_requireAjax = false;
    protected $_autoSetAjax = true;
    protected $_requireHttpMethods = array();
    protected $_httpResponseStatusCode = null;
    protected $_httpProtocol = null;
    protected $_security = array();
    protected $_rules = array();
    protected $_actions = array();

    public function __construct($name, $controller) {
        $this->setName($name);
        $this->setController($controller);
    }

    public function setName($name) {
        // Check name
        if (!Validate::isVariableName($name))
            throw new \Exception('Route name must be a valid variable');

        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function setController($controller) {
        if (!is_string($controller))
            throw new \Exception('Route controller parameter must be a string');

        $this->_controller = $controller;
    }

    public function getController() {
        return $this->_controller;
    }

    public function setRequireSsl($requireSsl) {
        if (!is_bool($requireSsl))
            throw new \Exception('Route requireSsl parameter must be a boolean');

        return $this->_requireSsl = $requireSsl;
    }

    public function getRequireSsl() {
        return $this->_requireSsl;
    }

    public function setRegex($regex) {
        if (!is_bool($regex))
            throw new \Exception('Route regex parameter must be a boolean');

        $this->_regex = $regex;
    }

    public function getRegex() {
        return $this->_regex;
    }

    public function setRequireAjax($requireAjax) {
        if (!is_bool($requireAjax))
            throw new \Exception('Route requireAjax parameter must be a boolean');

        return $this->_requireAjax = $requireAjax;
    }

    public function getRequireAjax() {
        return $this->_requireAjax;
    }

    public function setAutoSetAjax($autoSetAjax) {
        if (!is_bool($autoSetAjax))
            throw new \Exception('Route autoSetAjax parameter must be a boolean');

        $this->_autoSetAjax = $autoSetAjax;
    }

    public function getAutoSetAjax() {
        return $this->_autoSetAjax;
    }

    public function setRequireHttpMethods($requireHttpMethods) {
        if (!is_array($requireHttpMethods))
            throw new \Exception('Route requireHttpMethods parameter must an array');

        foreach ($requireHttpMethods as &$method) {
            if (!Method::isValid($method))
                throw new \Exception('Route requireHttpMethod parameter must null or a valid HTTP METHOD');
        }

        $this->_requireHttpMethods = $requireHttpMethods;
    }

    public function getRequireHttpMethods() {
        return $this->_requireHttpMethods;
    }

    public function setHttpResponseStatusCode($httpResponseStatusCode) {
        if (!is_null($httpResponseStatusCode) && !ResponseCode::isValid($httpResponseStatusCode))
            throw new \Exception('Route httpResponseStatusCode parameter must null or a valid HTTP ResponseCode');

        $this->_httpResponseStatusCode = $httpResponseStatusCode;
    }

    public function getHttpResponseStatusCode() {
        return $this->_httpResponseStatusCode;
    }

    public function setHttpProtocol($httpProtocol) {
        if (!is_null($httpProtocol) && !Protocol::isValid($httpProtocol))
            throw new \Exception('Route httpProtocol parameter must null or a valid HTTP ResponseCode');

        $this->_httpProtocol = $httpProtocol;
    }

    public function getHttpProtocol() {
        return $this->_httpProtocol;
    }

    public function setSecurity($security) {
        if (!is_array($security))
            throw new \Exception('Route security parameter must an array');

        $this->_security = $security;
    }

    public function getSecurity() {
        return $this->_security;
    }

    public function setRules($rules) {
        if (!is_array($rules))
            throw new \Exception('Route rules parameter must be an array');

        $this->_rules = $rules;
    }

    public function getRules() {
        return $this->_rules;
    }

    public function setActions($actions) {
        if (!is_array($actions))
            throw new \Exception('Route actions parameter must be an array');
        foreach ($actions as $actionName => $actionParameters) {
            if (!is_string($actionName))
                throw new \Exception('Action name must be a string');
            if (!is_array($actionParameters))
                throw new \Exception('Action parameters must be an array');
        }

        $this->_actions = $actions;
    }

    public function getActions() {
        return $this->_actions;
    }

}

?>