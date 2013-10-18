<?php

//session_get_cookie_params()
// TODO handlers : validator, encrypter, compressor, cache, save (file, db) etc ...
//todo add lock when read key, and implement lock time

namespace framework;

use framework\Cli;
use framework\utility\Validate;
use framework\Logger;
use framework\utility\Tools;
use framework\network\Http;

class Session {

    use \framework\debugger\Debug;

    protected static $_instance = false;
    protected static $_securise = true;
    protected static $_securityKeyName = 'securityHash';
    protected static $_state = false;
    protected $_lockedkeys = array();

    public static function setSecurise($securise) {
        if (!is_bool($securise))
            throw new \Exception('Securise parameter must be a boolean');
        if (self::isStarted())
            throw new \Exception('Session il already started, setting security parameter is not allowed');

        self::$_securise = $securise;
    }

    public static function getSecurise() {
        return self::$_securise;
    }

    public static function setSecurityKeyName($keyName) {
        if (!Validate::isVariableName($keyName))
            throw new \Exception('Session security key name must be a valid key name');

        self::$_securityKeyName = $keyName;
    }

    public static function getSecurityKeyName() {
        return self::$_securityKeyName;
    }

    protected function __construct() {
        if (!extension_loaded('session'))
            throw new \Exception('Session extension not loaded try change your PHP configuration');
        if (self::getDebug()) {
            Logger::getInstance()->debug('Session class has been instantiated', 'session');
            if (Cli::isCli())
                Logger::getInstance()->debug('Use session on cli', 'session');
        }
        // Securise
        if (self::getSecurise()) {
            $this->add(self::getSecurityKeyName(), $this->_generateSecurity(), true, true);
            if (self::getDebug())
                Logger::getInstance()->debug('Session was securised', 'session');
        }
    }

    public function __destruct() {
        if (self::getDebug()) {
            //TODO
            // Dump of session  $_SESSION ?
            // count number of key registered into a session
            // Koctets sessions ?
            // locked key ?
            // benchmark ? ...
        }
    }

    public static function setDebug($bool) {
        if (!is_bool($bool))
            throw new \Exception('debug parameter must be a boolean');
        self::$_debug = $bool;

        if ($bool)
            Logger::getInstance()->addGroup('session', 'Session report', true);
    }

    public static function getInstance($autoStart = true, $debug = false, $startOptions = array()) {
        if (!is_bool($autoStart))
            throw new \Exception('autoStart parameter must be a boolean');
        if (!is_bool($debug))
            throw new \Exception('debug parameter must be a boolean');
        // Start debug
        if ($debug)
            self::setDebug($debug);
        // Start session
        if ($autoStart) {
            if (!is_array($startOptions))
                throw new \Exception('startOptions parameter must be an array');
            // check options
            $id = isset($startOptions['id']) ? $startOptions['id'] : null;
            $name = isset($startOptions['name']) ? $startOptions['name'] : null;
            self::start($id, $name);
        }
        // Check instance, and create if not exists
        if (!isset(self::$_instance) || !self::$_instance instanceof Session || !self::$_instance->_securityCheck())
            self::$_instance = new Session();

        return self::$_instance;
    }

    protected function _generateSecurity() {
        self::_checkState();
        return sha1(Tools::getUserIp() . Http::getServer('HTTP_USER_AGENT'));
    }

    protected function _securityCheck() {
        // Security off ...
        if (!self::getSecurise())
            return true;

        self::_checkState();
        return ($this->_generateSecurity() === $this->get(self::getSecurityKeyName()));
    }

    protected static function _checkState() {
        if (!self::isStarted())
            throw new \Exception('Session must be started before use');
    }

    public static function isStarted() {
        return self::$_state;
    }

    public static function start($id = null, $name = null) {
        if (!self::isStarted()) {
            if (session_status() == PHP_SESSION_DISABLED)
                throw new \Exception('Session isn\'t started and cannot be started because session is disabled');
            if (!headers_sent()) {
                if (session_id() == '')
                    session_start();
                self::$_state = true;
                if (self::getDebug())
                    Logger::getInstance()->debug('Session is started', 'session');

                // set name and id
                if (!is_null($id))
                    self::setId($id);
                if (!is_null($name))
                    self::setName($name);
            }
            else
                throw new \Exception('Session isn\'t started and cannot be started because header is already send');
        }
    }

    public function setId($id) {
        if (!is_string($id))
            throw new \Exception('id parameter must be a string');
        if (!preg_match('`^[a-zA-Z0-9]*$`', $id))
            throw new \Exception('id parameter must be a valid string: [a-zA-Z0-9]');

        session_id($id);
        if (self::getDebug())
            Logger::getInstance()->debug('set session id', 'session');
    }

    public function getId() {
        return session_id();
    }

    public function setName($name) {
        if (!is_string($name))
            throw new \Exception('name parameter must be a string');
        session_name($name);

        if (self::getDebug())
            Logger::getInstance()->debug('Set session name', 'session');
    }

    public function getName() {
        return session_name();
    }

    public function regenerateId() {
        if (!self::isStarted())
            self::start();
        else {
            session_regenerate_id();
            if (self::getDebug())
                Logger::getInstance()->debug('Regenerate session id', 'session');
        }
    }

    public static function setSaveDirectory($dir, $forceCreate = true) {
        // TODO Check if is started session ?
        if ($forceCreate && !is_dir($dir)) {
            if (!mkdir($dir, 0775, true))
                throw new \Exception('Error on creating "' . $dir . '" directory');
        }else {
            if (!is_dir($dir))
                throw new \Exception('Directory "' . $dir . '" do not exists');
        }
        if (!is_writable($dir))
            throw new \Exception('Directory "' . $dir . '" is not writable');
        session_save_path(realpath($dir) . DS);
    }

    public static function getSaveDirectory() {
        return session_save_path();
    }

    public function add($key, $value, $forceReplace = false, $stayLock = false) {
        self::_checkState();
        if (!Validate::isVariableName($key))
            throw new \Exception('The key must be a valid key name');

        if (isset($_SESSION[$key]) && !$forceReplace)
            throw new \Exception('key : "' . $key . '" already exists in session');

        $this->lock($key);
        $_SESSION[$key] = $value;
        if (self::getDebug())
            Logger::getInstance()->debug('New key registered into a session : "' . $key . '"', 'session');
        if (!$stayLock)
            $this->unlock($key);
        return $this;
    }

    public function delete($key, $forceUnlock = false) {
        self::_checkState();
        if (isset($_SESSION[$key])) {
            if ($this->isLocked($key)) {
                if ($forceUnlock)
                    $this->unlock($forceUnlock);
                else
                    throw new \Exception('Delete key : "' . $key . '" error, key is locked');
            }
            if (self::getDebug())
                Logger::getInstance()->debug('Delete key : "' . $key . '"', 'session');
            unset($_SESSION[$key]);
        }
        return $this;
    }

    public function get($key, $default = null) {
        self::_checkState();
        if (!isset($_SESSION[$key])) {
            if (self::getDebug())
                Logger::getInstance()->debug('Try getting an unregistered key into session : "' . $key . '"', 'session');
            return $default;
        }else {
            if (self::getDebug())
                Logger::getInstance()->debug('Getting key : "' . $key, 'session');
            return $_SESSION[$key];
        }
    }

    public function isLocked($key) {
        return array_key_exists($key, $this->_lockedKeys);
    }

    public function lock($key) {
        if (!Validate::isVariableName($key))
            throw new \Exception('The key must be a valid key name');
        $this->_lockedKeys[$key] = true;
    }

    public function unlock($key) {
        if ($this->isLocked($key))
            unset($this->_lockedKeys[$key]);

        return $this;
    }

    public function getLockedKeys() {
        return $this->_lockedKeys;
    }

    public function write() {
        self::_checkState();
        session_write_close();
        if (self::getDebug())
            Logger::getInstance()->debug('Session has been written', 'session');
    }

    public function destroy() {
        self::_checkState();

        /*
         * TODO must be completed
         * Pour détruire complètement une session, comme faire sortir l'utilisateur, 
         * l'identifiant de la session doit également être effacé. 
         * Si un cookie est utilisé pour propager l'identifiant de session (comportement par défaut), 
         * alors le cookie de session doit être effacé. La fonction setcookie() peut être utilisée pour cela.
         */
        if (!session_destroy())
            throw new \Exception('Error during destroy session');

        if (self::getDebug())
            Logger::getInstance()->debug('Session has been destroyed', 'session');
    }

    public function increment($key, $offset = 1, $startValue = 1) {
        $this->_crement($key, $offset, true, $startValue);
        if (self::getDebug())
            Logger::getInstance()->debug('Increment : "' . $key . '"', 'session');
    }

    public function decrement($key, $offset = 1, $startValue = 1) {
        $this->_crement($key, $offset, false, $startValue);
        if (self::getDebug())
            Logger::getInstance()->debug('Decrement : "' . $key . '"', 'session');
    }

    protected function _crement($key, $offset = 1, $increment = true, $startValue = 1) {
        self::_checkState();
        $val = $this->get($key);

        if (is_null($val)) {
            if (!is_int($startValue) || $startValue >= 0)
                throw new \Exception('startValue must be an int');
            $this->add($key, $startValue, true);
            return;
        }
        if (!is_int($offset) || $offset == 0)
            throw new \Exception('Offset must be an int');
        if (!is_int($val))
            throw new \Exception('Key value must be an int');

        $increment = $increment ? $val + $offset : $val - $offset;
        $this->add($key, $increment, true);
    }

}

?>