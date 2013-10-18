<?php

// TODO rework with strategy pattern ?

namespace framework;

use framework\config\Constant;
use framework\config\Url;
use framework\Logger;
use framework\utility\Tools;

class Config {

    use pattern\Singleton,
        debugger\Debug;

    const XML = 'xml'; // conf format
    const INI = 'ini';
    // conf type
    const CONTROLLER = 'controller';
    const CONSTANT = 'constant';
    const URL = 'url';
    const TEMPLATE = 'template';
    const SECURITY = 'security';
    const DATABASE = 'database';
    const CACHE = 'cache';

    // for edit conf file
    const ADD = 1;
    const UPDATE = 2;
    const DEL = 3;
    const REWRITE_URLS = 4;

    protected static $_urls = null;
    protected static $_caches = array();
    protected static $_constants = array();

    protected function __construct() {
        $this->_loadDefaultFiles(PATH_CONFIG_DEFAULT);

        //load hostname config files (overload conf)
        $hostname = gethostname();
        if ($hostname && is_dir(PATH_CONFIG . $hostname))
            $this->_loadDefaultFiles(PATH_CONFIG . $hostname . DS);

        // Define default constants
        Constant::defineCons();
    }

    protected function _loadDefaultFiles($path) {
        if (file_exists($path . 'constants.xml'))
            $this->load($path . 'constants.xml', Config::CONSTANT, Config::XML, false);
        if (file_exists($path . 'urls.xml'))
            $this->load($path . 'urls.xml', Config::URL, Config::XML);
        // Optional config
        if (file_exists($path . 'templates.xml'))
            $this->load($path . 'templates.xml', Config::TEMPLATE, Config::XML);
        if (file_exists($path . 'security.xml'))
            $this->load($path . 'security.xml', Config::SECURITY, Config::XML);
        if (file_exists($path . 'databases.xml'))
            $this->load($path . 'databases.xml', Config::DATABASE, Config::XML);
        if (file_exists($path . 'caches.xml'))
            $this->load($path . 'caches.xml', Config::CACHE, Config::XML);
    }

    public function load($filename, $type, $format = self::XML, $loadArgs = null) {
        $className = 'framework\config\\' . ucfirst($type);
        if (!class_exists($className))
            throw new \Exception('Invalid config type');

        $conf = new $className($filename, $format);
        $conf->load($loadArgs);
    }

    // Urls
    public static function getUrl($urlName, $onlyValue = true, $params = array()) {
        if (!property_exists(self::$_urls, $urlName)) {
            Logger::getInstance()->debug('Url ' . $urlName . ' is not setted');
            return null;
        }

        $urlInfos = self::$_urls->$urlName;
        if ($onlyValue) {
            $value = $urlInfos['value'];
            //Generate url
            if (is_null($value))
                $value = Url::_generateUrlValue($urlInfos, $urlName);
            $i = 1;
            foreach ($params as &$param) {
                if ($i == 1)
                    $value = rtrim($value, '/');
                $value .= ($urlInfos['rewrite'] || (defined('REWRITE_URLS') && REWRITE_URLS)) ? '/' . $param : '&amp;parameter' . $i . '=' . Tools::stringToUrl($param);
                $i++;
            }
            return ($urlName != 'root') ? self::$_urls->root['value'] . $value : $value;
        }
        else
            $urlInfos['params'] = $params;

        return $urlInfos;
    }

    public static function getUrls($onlyValue = false) {
        if ($onlyValue) {
            $urls = new \stdClass();
            foreach (self::$_urls as $urlName => $urlDatas)
                $urls->$urlName = self::getUrl($urlName);

            return $urls;
        }
        return self::$_urls;
    }

    // Caches
    public static function getCache($name) {
        if (!is_string($name))
            throw new \Exception('Cache name must be a string');
        if (!array_key_exists($name, self::$_caches)) {
            Logger::getInstance()->debug('Cache ' . $name . ' is not setted');
            return false;
        }

        return self::$_caches[$name];
    }

    public static function getCaches() {
        return self::$_caches;
    }

}

?>