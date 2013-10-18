<?php

// TODO : set and get urlKeyController && urlKeyAction
// TODO : rename en rework class router...
// TODO : language support ?

namespace framework\mvc;

use framework\utility\Tools\Validate;
use framework\Logger;
use framework\network\Http;
use framework\network\http\ResponseCode;
use framework\network\http\Header;
use framework\Config;
use framework\utility\Benchmark;
use framework\Session;
use framework\mvc\Template;

class Dispatcher {

    use \framework\pattern\Singleton,
        \framework\debugger\Debug;

    protected $_controllersNamespace = 'controllers';
    protected $_namespaceSeparator = '\\';
    protected $_urlKeyController = 'controller';
    protected $_urlKeyAction = 'action';
    protected $_controllersPath = null;
    //request infos
    protected $_checkAction = true;
    protected $_currentRequest = null;
    protected $_currentController = null;
    protected $_currentControllerName = null;

    protected function __construct($controllersPath = null, $debug = false) {
        if (!is_null($controllersPath))
            $this->setControllersPath($controllersPath);

        if ($debug) {
            self::setDebug($debug);
            Benchmark::getInstance('dispatcher')->startTime(2)->startRam(2);
            Logger::getInstance()->addGroup('dispatcher', 'Dispatcher Benchmark and Informations', true);
        }
        // TODO check urls config, get KeyController/Action and set this +DEBUG mode
    }

    public function setControllersPath($path, $forceCreate = true) {
        if ($forceCreate && !is_dir($path)) {
            if (!mkdir($path, 0775, true))
                throw new \Exception('Error on creating "' . $path . '" directory');
        }else {
            if (!is_dir($path))
                throw new \Exception('Directory "' . $path . '" do not exists');
        }

        $this->_controllersPath = realpath($path) . DS;
    }

    public function getControllersPath() {
        return $this->_controllersPath;
    }

    public function setControllersNamespace($namespace, $namespaceSeparator = '\\') {
        if (!is_string($namespace))
            throw new \Exception('Controllers namespace must a string');
        $this->_controllersNamespace = $namespace;
        if (!is_string($namespaceSeparator))
            throw new \Exception('Namespace separator must be must a string');
        $this->_namespaceSeparator = $namespaceSeparator;
    }

    public function getControllersNamespace($withSeparator = false) {
        $ns = $this->_controllersNamespace;
        if ($withSeparator)
            $ns .= $this->getNamespaceSeparator();
        return $ns;
    }

    public function getNamespaceSeparator() {
        return $this->_namespaceSeparator;
    }

    public function getRootUrlControllerName($withControllersNamespace = false) {
        $rootUrl = Config::getUrl('root', false);
        if (!is_null($rootUrl)) {
            if ($withControllersNamespace)
                return $this->getControllersNamespace(true) . ucfirst($rootUrl['controller']);

            return ucfirst($rootUrl['controller']);
        }
        else
            return '';
    }

    public function getCurrentRequest($onlyUrlName = false) {
        if ($onlyUrlName) {
            if (isset($this->_currentRequest->urlName))
                return $this->_currentRequest->urlName;
            else
                return null;
        }
        return $this->_currentRequest;
    }

    public function getCurrentController($onlyName = false) {
        if ($onlyName)
            return $this->_currentControllerName;

        return $this->_currentController;
    }

    public function setCheckControllerAction($check) {
        if (!is_bool($check))
            throw new \Exception('Check parameter must an boolean');
        $this->_checkControllerAction = $check;
    }

    public function getCheckAction() {
        return $this->_checkAction;
    }

    public function setUrlKeyController($key) {
        if (!is_int($key) && !Validate::isVariableName($key))
            throw new \Exception('Url parameter name must be an integer or a valid variable name');
        $this->_urlKeyController = $key;
    }

    public function setUrlKeyAction($key) {
        if (!is_int($key) && !Validate::isVariableName($key))
            throw new \Exception('Url parameter name must be an integer or a valid variable name');
        $this->_urlKeyAction = $key;
    }

    public function getUrlKeyController() {
        return $this->_urlKeyController;
    }

    public function getUrlKeyAction() {
        return $this->_urlKeyAction;
    }

    public function run() {
        // Check maitenance mode activated
        if (defined('SITE_MAINTENANCE') && SITE_MAINTENANCE) {
            //Check if is admin request
            $adminUrl = Config::getUrl('admin', false);
            $adminQuery = $adminUrl ? stripos(Http::getQuery($this->_urlKeyController), $adminUrl['controller']) !== false : false;
            if (!Session::getInstance()->get('admin') && !$adminQuery)
                $this->show503(true);
        }

        $controller = Http::getQuery($this->_urlKeyController) ? Http::getQuery($this->_urlKeyController) : $this->getRootUrlControllerName();
        $action = Http::getQuery($this->_urlKeyAction) ? Http::getQuery($this->_urlKeyAction) : null;
        if (file_exists($this->getControllersPath() . ucfirst($controller) . '.class.php')) {
            //Set current request
            $urls = Config::getUrls();
            foreach ($urls as $urlName => $urlDatas) {
                if (ucfirst($controller) == ucfirst($urlDatas['controller']) && $action == $urlDatas['action']) {
                    $this->_currentRequest = new \stdClass();
                    $this->_currentRequest->urlName = $urlName;
                    $this->_currentRequest->value = $urlDatas['value'];
                    $this->_currentRequest->controller = $controller;
                    $this->_currentRequest->action = $action;
                    break;
                }
            }
            $this->_runController($this->getControllersNamespace(true) . ucfirst($controller), $action);
        } else {
            $this->show404();
            if (self::getDebug())
                Logger::getInstance()->debug('The url request a nonexistent controller : "' . $controller . '.class.php"', 'dispatcher');
        }
    }

    public function show401($die = false) {
        // TODO add possibility to use different http protocol ?
        Header::setResponseStatusCode(ResponseCode::CODE_UNAUTHORIZED, true);

        // Run controller url "httpError401"
        $this->_runErrorController('httpError401');

        if ($die)
            exit();
    }

    public function show403($die = false) {
        // TODO add possibility to use different http protocol ?
        // Use http protocol 1.0
        // And http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        // If use Http 1.1 protocol, header connection is keep-alive, else is close
        Header::setResponseStatusCode(ResponseCode::CODE_FORBIDDEN, true, true, Http::PROTOCOL_VERSION_1_0);

        // Run controller url "httpError403"
        $this->_runErrorController('httpError403');

        if ($die)
            exit();
    }

    public function show404($die = false) {
        // TODO add possibility to use different http protocol ?
        // Set Header
        // Use http protocol 1.0 look this : http://stackoverflow.com/questions/2769371/404-header-http-1-0-or-1-1
        // And http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        // If use Http 1.1 protocol, header connection is keep-alive, else is close
        Header::setResponseStatusCode(ResponseCode::CODE_NOT_FOUND, true, true, Http::PROTOCOL_VERSION_1_0);

        // Run controller url "httpError404"
        $this->_runErrorController('httpError404');

        if ($die)
            exit();
    }

    public function show500($die = false) {
        // TODO add possibility to use different http protocol ?
        Header::setResponseStatusCode(ResponseCode::CODE_INTERNAL_SERVER_ERROR, true);
        // Run controller url "httpError500"
        $this->_runErrorController('httpError500');

        if ($die)
            exit();
    }

    public function show503($die = false) {
        // TODO add possibility to use different http protocol ?
        Header::setResponseStatusCode(ResponseCode::CODE_SERVICE_UNAVAILABLE, true);

        // Run controller url "httpError403"
        $this->_runErrorController('httpError503');

        if ($die)
            exit();
    }

    public function showDebugger($die = false) {
        //Header::setResponseStatusCode(ResponseCode::CODE_SERVICE_UNAVAILABLE, true);
        // Run controller url "debugger"
        $this->_runErrorController('debugger');
        if ($die)
            exit();
    }

    protected function _checkController($controller, $returnValidity = false) {
        $inst = new \ReflectionClass($controller);
        if ($returnValidity)
            return (file_exists($this->getControllersPath() . ucfirst($controller) . '.class.php') && !$inst->isInterface() || !$inst->isAbstract() || $inst->hasMethod('initTemplate') || $inst->hasMethod('display'));

        if ($inst->isInterface() || $inst->isAbstract())
            throw new \Exception('Controller "' . $controller . '" cannot be an interface of an abstract class');
        if (!$inst->hasMethod('initTemplate'))
            throw new \Exception('Controller "' . $controller . '" must be implement method "initTemplate("');
        if (!$inst->hasMethod('display'))
            throw new \Exception('Controller "' . $controller . '" must be implement method "Diplay"');
    }

    protected function _checkActionController($controller, $action, $controllerName = null, $returnValidity = false) {
        if (!method_exists($controller, $action)) {
            if ($returnValidity)
                return false;

            // Show 404 with die script, and log
            if (self::getDebug())
                Logger::getInstance()->debug('Method : "' . $action . '" don\'t exists on controller "' . $controllerName . '"', 'dispatcher');
            $this->show404(true);
        }

        return true;
    }

    protected function _runController($controller, $action, $check = true, $controllerInst = false) {
        // Cast controller name
        $controller = (string) $controller;

        if ($check) {
            if (self::getDebug())
                Logger::getInstance()->debug('Try check controller : "' . $controller . '"', 'dispatcher');
            $this->_checkController($controller);
            if (self::getDebug())
                Logger::getInstance()->debug('Controller : "' . $controller . '" was checked', 'dispatcher');
        }

        // Instantiate the controller if not already instantiated in argument of function
        if (is_object($controllerInst)) {
            if (self::getDebug())
                Logger::getInstance()->debug('Controller :  "' . $controller . '" already instantiated', 'dispatcher');
            $ctrl = $controllerInst;
        } else {
            if (self::getDebug())
                Logger::getInstance()->debug('Try run controller :  "' . $controller . '"', 'dispatcher');
            $ctrl = new $controller();
            if (self::getDebug())
                Logger::getInstance()->debug('Controller : "' . $controller . '" was run', 'dispatcher');
        }

        // Init template, only if display auto is on, and if is not already initialized
        if (Template::getTemplate() && !$ctrl->isTemplateInitialized() && $ctrl->getAutoCallDisplay()) {
            if (self::getDebug())
                Logger::getInstance()->debug('Dispatcher initialize template', 'dispatcher');
            $ctrl->initTemplate();
        }

        // Call action, if is defined
        if (!is_null($action)) {
            if ($this->getCheckAction() && $check) {
                if (self::getDebug())
                    Logger::getInstance()->debug('Check action : "' . $action . '"', 'dispatcher');
                $this->_checkActionController($ctrl, $action, $controller);
                if (self::getDebug())
                    Logger::getInstance()->debug('Action : "' . $action . '" was checked', 'dispatcher');
            }
            if (self::getDebug())
                Logger::getInstance()->debug('Try call action : "' . $action . '"', 'dispatcher');
            $ctrl->$action();
            if (self::getDebug())
                Logger::getInstance()->debug('Action : "' . $action . '" was called', 'dispatcher');
        }


        // Set dispatched controller object and name
        $this->_currentController = $ctrl;
        $this->_currentControllerName = $controller;

        // Display template
        if (Template::getTemplate() && $ctrl->isTemplateInitialized() && $ctrl->getAutoCallDisplay()) {
            if (self::getDebug())
                Logger::getInstance()->debug('Try call method "display"', 'dispatcher');
            $ctrl->display();
            if (self::getDebug())
                Logger::getInstance()->debug('Method "display" was called', 'dispatcher');
        }

        if (self::getDebug())
            Logger::getInstance()->debug('Was fully run', 'dispatcher');
    }

    protected function _runErrorController($url) {
        $url = Config::getUrl($url, false);
        if (!is_null($url)) {
            if (self::getDebug())
                Logger::getInstance()->debug('Try check Error controller : "' . $this->getControllersNamespace(true) . $url['controller'] . '"', 'dispatcher');
            if ($this->_checkController($this->getControllersNamespace(true) . ucfirst($url['controller']), true)) {
                if (self::getDebug())
                    Logger::getInstance()->debug('Error controller : "' . $this->getControllersNamespace(true) . $url['controller'] . '" was checked', 'dispatcher');
                $fullName = $this->getControllersNamespace(true) . ucfirst($url['controller']);
                if (self::getDebug())
                    Logger::getInstance()->debug('Try run Error controller :  "' . $fullName . '"', 'dispatcher');
                $inst = new $fullName();
                if (self::getDebug())
                    Logger::getInstance()->debug('Error controller :  "' . $fullName . '" was run', 'dispatcher');
                if (self::getDebug())
                    Logger::getInstance()->debug('Try check action :  "' . $url['action'] . '"', 'dispatcher');
                if ($this->_checkActionController($inst, $url['action'], null, true)) {
                    if (self::getDebug())
                        Logger::getInstance()->debug('Action :  "' . $url['action'] . '" was checked', 'dispatcher');
                    $this->_runController($this->getControllersNamespace(true) . ucfirst($url['controller']), $url['action'], false, $inst);
                }
            }
        }
        //TODO DEBUG trace ?
    }

}

?>