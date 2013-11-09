<?php

use framework\Autoloader;
use framework\Config;
use framework\Session;
use framework\Security;
use framework\Logger;
use framework\Language;
use framework\autoloader\Globalizer;
use framework\error\ErrorManager;
use framework\error\ExceptionManager;
use framework\error\observers\Display;
use framework\error\observers\Log;
use framework\logger\observers\Write;
use framework\logger\observers\Mail;
use framework\mvc\Template;
use framework\mvc\Router;
use framework\utility\Cookie;
use framework\utility\Date;
use framework\utility\Benchmark;

// Start benchmark
$bench = array('time' => microtime(true), 'ram' => memory_get_usage());

// Load config
Config::setPath(PATH_CONFIG);
Config::getInstance();


if (defined('ENVIRONNEMENT'))
    static::setEnv(ENVIRONNEMENT);

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

// Start benchmark
if (static::getProfiler()) {
    Benchmark::getInstance('global')
            ->startTime(Benchmark::TIME_MS, $bench['time'])
            ->startRam(Benchmark::RAM_MB, $bench['ram']);
}

// Exception, Error and Logger management
$exc = ExceptionManager::getInstance()->start();
$err = ErrorManager::getInstance()->start(true, static::getDebug(), static::getDebug());
$log = Logger::getInstance();

// Set language
if (!defined('PATH_LANGUAGE'))
    throw new \Exception('Miss language path datas');
Language::setDatasPath(PATH_LANGUAGE);
$language = Language::getInstance();
if (!defined('LANGUAGE_DEFAULT'))
    throw new \Exception('Miss language default');
$language->setLanguage(LANGUAGE_DEFAULT, true, true);

// Set default template
if (defined('TEMPLATE_DEFAULT'))
    Template::setTemplate(TEMPLATE_DEFAULT);

//Enable debug tools
if (static::getDebug()) {
    $log->setLevel(Logger::DEBUG);
    Autoloader::setDebug(true);
    //Debug error and exeception
    $exc->attach(new Display());
    $err->attach(new Display());
}


// Logger parameters
if (defined('LOGGER_CACHE') && LOGGER_CACHE && !static::getDebug())
    $log->setCache(LOGGER_CACHE);
if (defined('LOGGER_LEVEL') && !static::getDebug())
    $log->setLevel(LOGGER_LEVEL);
if (defined('LOGGER_BACKTRACE') && LOGGER_BACKTRACE)
    $log->setLogBackTrace(LOGGER_BACKTRACE);
if (defined('LOGGER_WRITE') && LOGGER_WRITE && !static::getDebug())
    $log->attach(new Write(PATH_LOGS), 'writer');
// firebug, display, chrome...
if (defined('LOGGER_DISPLAY') && LOGGER_DISPLAY && static::getDebug()) {
    $observers = is_string(LOGGER_DISPLAY) ? explode(',', LOGGER_DISPLAY) : LOGGER_DISPLAY;
    foreach ($observers as &$observer) {
        $name = '\framework\logger\observers\\' . ucfirst($observer);
        if (class_exists($name))
            $log->attach(new $name(), $observer);
    }
}
if (defined('LOGGER_MAIL') && LOGGER_MAIL && defined('LOGGER_MAIL_TO_EMAIL') && defined('LOGGER_MAIL_TO_NAME') && !static::getDebug()) {
    $mailConfig = array(
        'fromEmail' => ADMIN_EMAIL,
        'fromName' => $language->getVar('site_name'),
        'toEmail' => LOGGER_MAIL_TO_EMAIL, 'toName' => LOGGER_MAIL_TO_NAME,
        'mailSubject' => $language->getVar('site_name') . '  logs'
    );
    $log->attach(new Mail($mailConfig, ADMIN_EMAIL));
}

if (defined('LOGGER_ERROR') && LOGGER_ERROR) {
    $exc->attach(new Log());
    $err->attach(new Log());
}

// Config router host
if (!defined('HOSTNAME'))
    throw new \Exception('Miss hostname constant');
Router::setHost(HOSTNAME);

// Setting
if (defined('TIMEZONE'))
    Date::setDateDefaultTimezone(TIMEZONE);

// Auto set language, by session
$lang = Session::getInstance()->get('language');
if (!is_null($lang) && $lang != Language::getInstance()->getLanguage())
    $language->setLanguage($lang);
// Auto set language, by cookie
$lang = Cookie::get('language');
if (!is_null($lang) && $lang != Language::getInstance()->getLanguage())
    $language->setLanguage($lang);

// Security
Security::autorun();

// Clean
unset($bench, $globalizer, $language, $exc, $err, $log);
?>
