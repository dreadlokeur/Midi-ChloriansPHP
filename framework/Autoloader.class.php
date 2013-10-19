<?php

// Pear/Phar/PECL loader, and  ClassMapLoader : class loader which uses a predefined map of classes and their paths. Provides greater performance at the cost of flexibility. // inspire by https://github.com/OPL/opl3-autoloader
// Singleton ?

namespace framework;

use framework\Logger;
use framework\Cache;

class Autoloader {

    use autoloader\Classes,
        autoloader\Directories,
        autoloader\Namespaces;

    protected static $_autoloaders = array();
    protected static $_debug = false;
    protected static $_logs = array();
    protected static $_benchmarkTime = 0;
    protected static $_benchmarkMemory = 0;
    protected static $_cache = false;

    public static function setCache($cacheName) {
        $cache = Cache::getCache($cacheName);
        if (!$cache)
            throw new \Exception('Invalid cache');
        self::$_cache = $cache;
    }

    public static function getCache() {
        return self::$_cache;
    }

    public function __construct($debug = false) {
        if ($debug)
            self::setDebug($debug);
    }

    public function __destruct() {
        if (self::getDebug()) {
            $logs = self::getLogs();
            if (!empty($logs)) {
                Logger::getInstance()->addGroup('autoloader', 'Autoloader report', true);

                foreach ($logs as &$log)
                    Logger::getInstance()->debug($log, 'autoloader');

                Logger::getInstance()->debug(count(self::getAutoloaders()) . ' autoloader drivers, ' . count(self::getDirectories()) . ' directories and ' . count(self::getNamespaces()) . ' namespaces registered', 'autoloader');
                Logger::getInstance()->debug('Loading ' . count(self::getClasses()) . ' classes (' . self::countGlobalizedClasses() . ' globalized classes)  in aproximately ' . round(self::getBenchmark('time') * 1000, 4) . ' milli-seconds', 'autoloader');
                Logger::getInstance()->debug('Aproximately memory used  : ' . round(self::getBenchmark('memory') / 1024, 4) . ' kilo-octets', 'autoloader');
                // Avoid multi call
                self::$_logs = array();
                self::resetBenchmark();
                self::setDebug(false);
            }
        }
    }

    public static function setDebug($bool) {
        if (!is_bool($bool))
            throw new \Exception('debug parameter must be a boolean');
        self::$_debug = $bool;
    }

    public static function getDebug() {
        return self::$_debug;
    }

    public static function getLogs() {
        return self::$_logs;
    }

    public static function getBenchmark($type) {
        if (!is_string($type) && !$type == 'time' && !$type == 'memory')
            throw new \Exception('type parameter must be a string : time or memory');
        if ($type == 'time')
            return self::$_benchmarkTime;
        if ($type == 'memory')
            return self::$_benchmarkMemory;
    }

    public static function registerAutoloader($loaderName, $loaderArguments = array(), $throw = true, $prepend = false) {
        if (!is_string($loaderName))
            throw new \Exception('LoaderName parameter must be a string');

        if (class_exists('framework\autoloader\autoloaders\\' . $loaderName, false))
            $loaderClass = 'framework\autoloader\autoloaders\\' . $loaderName;
        else
            $loaderClass = $loaderName;


        // Instantiate loader
        $loaderInstance = new \ReflectionClass($loaderClass);
        if (!in_array('framework\autoloader\IAutoloaders', $loaderInstance->getInterfaceNames()))
            throw new \Exception('Loader class must be implement framework\autoloader\IAutoloaders');
        if ($loaderInstance->isAbstract())
            throw new \Exception('Loader class must be not abstract class');
        if ($loaderInstance->isInterface())
            throw new \Exception('Loader class must be not interface');

        // Check if is already registered
        if (self::_isRegisteredAutoloader($loaderInstance->getShortName()))
            throw new \Exception('Loader is already registered');

        // Checking arguments for create an instance with good parameters
        if (count($loaderArguments) > 1) {
            $loaderConstructor = new \ReflectionMethod($loaderClass, '__construct');
            $params = $loaderConstructor->getParameters();
            $cleanedLoaderArguments = array();
            foreach ($params as $key => $param) {
                if ($param->isPassedByReference())
                    $cleanedLoaderArguments[$key] = &$loaderArguments[$key];
                else
                    $cleanedLoaderArguments[$key] = $loaderArguments[$key];
            }
            $loader = $loaderInstance->newInstanceArgs($cleanedLoaderArguments);
        }
        else
            $loader = $loaderInstance->newInstance();


        // Register spl autoload
        if (!function_exists('spl_autoload_register'))
            throw new \Exception('spl_autoload_register does not exists in this PHP installation');
        if (!is_bool($throw))
            throw new \Exception('throw parameter must be an boolean');
        if (!is_bool($prepend))
            throw new \Exception('prepend parameter must be an boolean');

        spl_autoload_register(array($loader, 'autoload'), $throw, $prepend);
        // Stock
        self::$_autoloaders[$loaderInstance->getShortName()] = $loader;
    }

    public static function getAutoloader($loaderName) {
        if (!self::_isRegisteredAutoloader($loaderName))
            throw new \Exception('Loader isn\'t registered');

        return self::$_autoloaders[$loaderName];
    }

    public static function getAutoloaders() {
        return self::$_autoloaders;
    }

    public static function unregisterAutoloader($loaderName) {
        if (!self::_isRegisteredAutoloader($loaderName))
            throw new \Exception('Loader isn\'t registered');
        if (!function_exists('spl_autoload_unregister'))
            throw new \Exception('spl_autoload_unregister does not exists in this PHP installation');
        spl_autoload_unregister(array(self::$_autoloaders[$loaderName], 'autoload'));
        unset(self::$_autoloaders[$loaderName]);
    }

    public static function setAutoloadExtensions($exts) {
        if (!function_exists('spl_autoload_extensions'))
            throw new \Exception('spl_autoload_extensions does not exists in this PHP installation');

        if (is_array($exts)) {
            $extList = '';
            foreach ($exts as &$ext) {
                if (!is_string($ext))
                    throw new \Exception('Extension parameter must be a string');
                $extList .= '.' . $ext . ',';
            }
            if (!empty($extList))
                spl_autoload_extensions(trim($extList, ','));
        } elseif (is_string($exts))
            spl_autoload_extensions('.' . $exts);
        else
            throw new \Exception('Extensions parameter must be an array or a string');
    }

    public static function getAutoloadExtensions() {
        if (!function_exists('spl_autoload_extensions'))
            throw new \Exception('spl_autoload_extensions does not exists in this PHP installation');
        return spl_autoload_extensions();
    }

    public static function resetBenchmark($time = true, $memory = true) {
        if (!is_bool($time))
            throw new \Exception('time parameter must be a boolean');
        if (!is_bool($memory))
            throw new \Exception('time parameter must be a boolean');
        if ($time)
            self::$_benchmarkTime = 0;
        if ($memory)
            self::$_benchmarkMemory = 0;
    }

    protected static function _addLog($log) {
        if (!is_string($log))
            throw new \Exception('log parameter must be a string');
        self::$_logs[] = $log;
    }

    protected static function _isRegisteredAutoloader($autoloader) {
        return (array_key_exists($autoloader, self::getAutoloaders()));
    }

    protected static function _setBenchmark($time, $memory) {
        if (!is_float($time) && !is_int($time))
            throw new \Exception('time parameter must be an int or a float');
        if (!is_float($memory) && !is_int($memory))
            throw new \Exception('memory parameter must be an int or a float');

        self::$_benchmarkTime = self::$_benchmarkTime + $time;
        self::$_benchmarkMemory = self::$_benchmarkMemory + $memory;
    }

}

?>