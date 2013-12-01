<?php

namespace framework;

use framework\Cli;
use framework\mvc\Router;
use framework\error\ErrorManager;
use framework\error\ExceptionManager;

final class Application {

    use pattern\Singleton,
        debugger\Debug;

    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    protected static $_env = self::ENV_DEV;
    protected static $_profiler = true;
    protected $_isInit = false;
    protected $_isRun = false;
    protected static $_globalizeClassList = array(
        'framework\Config',
        'framework\config\Reader',
        'framework\config\Loader',
        'framework\mvc\Controller',
        'framework\Logger',
        'framework\mvc\Router',
        'framework\error\ErrorManager',
        'framework\error\ExceptionManager'
    );

    public static function getGlobalizeClassList() {
        return self::$_globalizeClassList;
    }

    public static function setEnv($env) {
        if ($env != self::ENV_DEV && $env != self::ENV_TEST && $env != self::ENV_PROD)
            throw new \Exception('Invalid environnement type');

        if ($env == self::ENV_DEV)
            self::setDebug(true);
        if ($env == self::ENV_DEV || $env == self::ENV_TEST)
            self::setProfiler(true);

        self::$_env = $env;
    }

    public static function getEnv() {
        return self::$_env;
    }

    public static function setProfiler($bool) {
        self::$_profiler = $bool;
    }

    public static function getProfiler() {
        return self::$_profiler;
    }

    protected function __construct($boostrapFile) {
        if (!file_exists($boostrapFile))
            throw new \Exception('Invalid bootstrap file');

        require $boostrapFile;

        $this->_isInit = true;
    }

    public function __destruct() {
        // Stop managers
        if ($this->_isInit) {
            ExceptionManager::getInstance()->stop();
            ErrorManager::getInstance()->stop();
        }
    }

    public function run() {
        if ($this->_isRun)
            throw new \Exception('Application already runned');
        //Cli
        if (Cli::isCli())
            throw new \Exception('CLI not yet');

        // Check maitenance mode activated => show503
        if (defined('SITE_MAINTENANCE') && SITE_MAINTENANCE)
            Router::getInstance()->show503(true);

        // Run router
        Router::getInstance()->run();
        $this->_isRun = true;
    }

}

?>