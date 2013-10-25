<?php

use framework\Autoloader;
use framework\autoloader\Globalizer;
use framework\Config;
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
use framework\Security;
use framework\Language;
use framework\mvc\Template;
use framework\mvc\Router;

$bench = array('time' => microtime(true), 'ram' => memory_get_usage());

// Load config
Config::setPath(PATH_CONFIG);
Config::getInstance();

if (defined('ENVIRONNEMENT'))
    static::setEnv(ENVIRONNEMENT);

// Set language
if (!defined('LANGUAGE_DEFAULT'))
    throw new \Exception('Miss language default');
$language = Language::getInstance();
$language->setDatasPath(PATH_LANGUAGE);
if (!defined('PATH_LANGUAGE'))
    throw new \Exception('Miss constant PATH_LANGUAGE');
$language->setLanguage(LANGUAGE_DEFAULT, true, true);

// Set default tpl
if (defined('TEMPLATE_DEFAULT'))
    Template::setTemplate(TEMPLATE_DEFAULT);


// Autoloader cache
if (defined('AUTOLOADER_CACHE') && !static::getDebug())
    Autoloader::setCache(AUTOLOADER_CACHE);
// Add vendors directory
Autoloader::addDirectory(PATH_VENDORS);

//Globalize essentials classes
if (defined('AUTOLOADER_GLOBALIZER') && AUTOLOADER_GLOBALIZER && !static::getDebug()) {
    $globalizer = new Globalizer(static::getGlobalizeClassList(), true);
    $globalizer->loadGlobalizedClass();
}

// Exception, Error and Logger management
$exc = ExceptionManager::getInstance()->start();
$err = ErrorManager::getInstance()->start(true, static::getDebug());
$log = Logger::getInstance();
if (LOGGER_CACHE && !static::getDebug())
    $this->_log->setCache(LOGGER_CACHE);


//Enable debug tools
if (static::getDebug() || static::getEnv() == static::ENV_DEV) {
    $log->setLevel(Logger::DEBUG);
    Autoloader::setDebug(true);

    // Attach observers error and exception manager for display et log erros and exceptions
    $exc->attach(new Display())->attach(new Log());
    $err->attach(new Display())->attach(new Log());
}

// Start benchmark
if (static::getProfiler()) {
    Benchmark::getInstance('global')
            ->startTime(Benchmark::TIME_MS, $bench['time'])
            ->startRam(Benchmark::RAM_MB, $bench['ram']);
}

// Logger parameters
if (defined('LOG_LEVEL') && !static::getDebug())
    $log->setLevel(LOG_LEVEL);
if (defined('LOG_BACKTRACE') && LOG_BACKTRACE)
    $log->setLogBackTrace(LOG_BACKTRACE);
// Add observers loggers, example: firebug, display, chrome
if (defined('LOG_DISPLAY') && LOG_DISPLAY && is_string(LOG_DISPLAY)) {
    $observers = explode(',', LOG_DISPLAY);
    foreach ($observers as $observer) {
        $name = '\framework\logger\observers\\' . ucfirst($observer);
        if (class_exists($name))
            $log->attach(new $name(), $observer);
    }
}

if (defined(MAIL_LOG) && MAIL_LOG && defined(LOG_MAIL_TO_EMAIL) && defined(LOG_MAIL_TO_NAME)) {
    $mailConfig = array(
        'fromEmail' => ADMIN_EMAIL,
        'fromName' => $language->getVar('site_name'),
        'toEmail' => LOG_MAIL_TO_EMAIL, 'toName' => LOG_MAIL_TO_NAME,
        'mailSubject' => $language->getVar('site_name') . '  log'
    );
    $exc->attach(new Log())->attach(new Mail($mailConfig, ADMIN_EMAIL));
    $err->attach(new Log())->attach(new Mail($mailConfig, ADMIN_EMAIL));
}
if (defined(LOG_WRITE) && LOG_WRITE)
    $log->attach(new Writer(PATH_LOGS), 'writer');


// Setting
Date::setDateDefaultTimezone(TIMEZONE);
if (!is_null(Session::getInstance()->get('language')))
    $language->setLanguage(Session::getInstance()->get('language'));

if (defined('HOSTNAME'))
    Router::setHost(HOSTNAME);

//Security
Security::autorun();
?>
