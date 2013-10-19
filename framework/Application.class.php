<?php

/**
 * Include necessary files, and setting class autoloader
 * 
 * @copyright  Copyright 2013 - MidichlorianPHP and contributors
 * @author     NAYRAND Jérémie (dreadlokeur) <dreadlokeur@gmail.com>
 * @version    1.0.1dev2
 * @license    GNU General Public License 3 http://www.gnu.org/licenses/gpl.html
 * @package    framework
 */

namespace framework;

use framework\Cli;
use framework\Autoloader;
use framework\autoloader\Globalizer;
use framework\Config;
use framework\mvc\Dispatcher;
use framework\utility\Benchmark;
use framework\Logger;
use framework\error\ErrorManager;
use framework\error\ExceptionManager;
use framework\error\observers\Display;
use framework\error\observers\Log;
use framework\error\observers\Mail;
use framework\utility\Date;
use framework\logger\observers\Writer;
use framework\Session;
use framework\security\Api;
use framework\Language;
use framework\mvc\Template;

class Application {

    use pattern\Singleton,
        debugger\Debug;

    const ENV_DEBUG = 'debug';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    protected static $_env = self::ENV_DEBUG;
    protected static $_profiler = true;
    protected $_config = null; //Config instance
    protected $_language = null;
    protected $_isRunned = false; //State
    protected $_ex = null; // Exception manager
    protected $_err = null; // Error manager
    protected $_log = false;
    protected $_bench = array('time' => null, 'ram' => null); //Benchmark
    protected $_globalizeClassList = array(
        'framework\Config',
        'framework\config\Url',
        'framework\config\Constant',
        'framework\mvc\Controller',
        'framework\Logger',
        'framework\mvc\Dispatcher',
        'framework\error\ErrorManager',
        'framework\error\ExceptionManager'
    );

    public static function setEnv($env) {
        if ($env != self::ENV_DEBUG && $env != self::ENV_TEST && $env != self::ENV_PROD)
            throw new \Exception('Invalid environnement type');

        if ($env == self::ENV_DEBUG)
            self::setDebug(true);
        if ($env == self::ENV_DEBUG || $env == self::ENV_TEST) {
            self::setProfiler(true);
        }

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

    protected function __construct() {
        // Start benchmark
        $this->_bench = array('time' => microtime(true), 'ram' => memory_get_usage());
        $this->_init();
    }

    public function __destruct() {
        // Stop managers
        if (!is_null($this->_exc))
            $this->_exc->stop();
        if (!is_null($this->_err))
            $this->_err->stop();
    }

    protected function _init() {
        // Load config
        $this->_config = Config::getInstance();

        if (defined('ENVIRONNEMENT'))
            self::setEnv(ENVIRONNEMENT);

        // Set language
        if (!defined('LANGUAGE_DEFAULT'))
            throw new \Exception('Miss language default');
        if (!defined('LANGUAGE_ACCEPTED'))
            throw new \Exception('Miss constant language accepted list');
        if (!defined('PATH_LANGUAGE'))
            throw new \Exception('Miss constant PATH_LANGUAGE');
        $this->_language = Language::getInstance();
        $this->_language->setLangs(LANGUAGE_ACCEPTED, PATH_LANGUAGE, LANGUAGE_DEFAULT);

        // Set default tpl
        if (defined('TEMPLATE_DEFAULT'))
            Template::setTemplate(TEMPLATE_DEFAULT);


        // Autoloader cache
        if (defined('AUTOLOADER_CACHE') && !self::getDebug())
            Autoloader::setCache(AUTOLOADER_CACHE);
        // Add vendors directory
        Autoloader::addDirectory(PATH_VENDORS);

        //Globalize essentials classes
        if (defined('AUTOLOADER_GLOBALIZER') && AUTOLOADER_GLOBALIZER && !self::getDebug()) {
            $globalizer = new Globalizer($this->_globalizeClassList, true);
            $globalizer->loadGlobalizedClass();
        }




        // Exception, Error and Logger management
        $this->_exc = ExceptionManager::getInstance()->start();
        $this->_err = ErrorManager::getInstance()->start(true, self::getDebug(), self::getDebug());
        $this->_log = Logger::getInstance();
        if (LOGGER_CACHE && !self::getDebug())
            $this->_log->setCache(LOGGER_CACHE);

        //Enable debug tools
        if (self::getDebug() || self::getEnv() == self::ENV_DEBUG) {
            // Set logger debug level and enable debug mode
            $this->_log->setDebug(true);
            $this->_log->setLevel(Logger::DEBUG);

            // Set config, autoloader, session manager, and dispatcher  debug mode enable
            $this->_config->setDebug(true);
            Autoloader::setDebug(true);
            Session::setDebug(true);
            Dispatcher::getInstance(array(PATH_CONTROLLERS, true));

            // Attach observers error and exception manager for display et log erros and exceptions
            $this->_exc->attach(new Display())->attach(new Log());
            $this->_err->attach(new Display())->attach(new Log());

            Api::setDebug(true);
        }
        // Start benchmark
        if (self::getProfiler()) {
            Benchmark::getInstance('global')
                    ->startTime(Benchmark::TIME_MILLISECOND, $this->_bench['time'])
                    ->startRam(Benchmark::RAM_KOCTET, $this->_bench['ram']);
        }

        // Logger parameters
        if (defined('LEVEL_LOG') && !self::getDebug())
            $this->_log->setLevel(LEVEL_LOG);
        if (defined('LEVEL_LOG_BACKTRACE') && LEVEL_LOG_BACKTRACE)
            $this->_log->setLogBackTrace(LEVEL_LOG_BACKTRACE);
        // Add observers loggers, example: firebug, display, chrome
        if (defined('DISPLAY_LOG') && DISPLAY_LOG && is_string(DISPLAY_LOG)) {
            $observers = explode(',', DISPLAY_LOG);
            foreach ($observers as $observer) {
                $name = '\framework\logger\observers\\' . ucfirst($observer);
                if (class_exists($name))
                    $this->_log->attach(new $name(), $observer);
            }
        }
        if (MAIL_LOG && MAIL_LOG_TO_EMAIL && MAIL_LOG_TO_NAME) {
            $mailConfig = array(
                'fromEmail' => ADMIN_EMAIL,
                'fromName' => $this->_config->getLanguageVar('site_name'),
                'toEmail' => MAIL_LOG_TO_EMAIL, 'toName' => MAIL_LOG_TO_NAME,
                'mailSubject' => $this->_config->getLanguageVar('site_name') . '  log'
            );
            $this->_exc->attach(new Log())->attach(new Mail($mailConfig, ADMIN_EMAIL));
            $this->_err->attach(new Log())->attach(new Mail($mailConfig, ADMIN_EMAIL));
        }
        if (WRITE_LOG)
            $this->_log->attach(new Writer(PATH_LOGS), 'writer');


        // Setting
        Date::setDateDefaultTimezone(TIMEZONE);
        if (!is_null(Session::getInstance()->get('language')))
            $this->_language->setLanguage(Session::getInstance()->get('language'));

        if (!defined('APP_INIT'))
            define('APP_INIT', true);
    }

    public function run() {
        if ($this->_isRunned)
            throw new \Exception('Application already runned');
        if (Cli::isCli())
            throw new \Exception('CLI not yet');

        // Run dispatcher : Catcher http request and instanciate controller
        $dispatcher = Dispatcher::getInstance(PATH_CONTROLLERS);
        $dispatcher->run();

        $this->_isRunned = true;
    }

}

?>
