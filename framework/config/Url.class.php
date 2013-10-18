<?php

namespace framework\config;

use framework\Config;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\Url as UrlManager;
use framework\network\Http;
use framework\Logger;

class Url extends Config {

    protected $_filename = null;
    protected $_format = null;

    public function __construct($filename, $format) {
        if (!file_exists($filename))
            throw new \Exception('File "' . $filename . '" don\'t exists');

        // Check format
        if ($format !== Config::XML && $format !== Config::INI)
            throw new \Exception('Invalid config format');

        $this->_filename = $filename;
        $this->_format = $format;
        if (is_null(self::$_urls))
            self::$_urls = new \stdClass();
    }

    public function load() {
        switch ($this->_format) {
            case self::XML:
                $xml = @simplexml_load_file($this->_filename);
                if ($xml === null || $xml === false)
                    throw new \Exception('Invalid xml file');
                // Racine
                if (isset($xml->rootUrl)) {
                    if (!isset($xml->rootUrl->value))
                        throw new \Exception('Url root value miss');
                    if (!isset($xml->rootUrl->controller))
                        throw new \Exception('Url root controller miss');

                    $value = Tools::castValue((string) $xml->rootUrl->value);
                    if (!Validate::isUrl($value))
                        throw new \Exception('Url root value must be a valid url');
                    $controller = (string) $xml->rootUrl->controller;
                    $action = (isset($xml->rootUrl->action)) ? (string) $xml->rootUrl->action : null;
                    $ssl = Validate::isUrl($value, true) ? true : false; // check if is ssl url

                    self::$_urls->root = array(
                        'value' => $value,
                        'file' => null,
                        'controller' => $controller,
                        'action' => $action,
                        'ssl' => $ssl,
                        'rewrite' => false);
                }

                // Others Urls
                foreach ($xml->url as $url) {
                    if (!isset($url->name))
                        throw new \Exception('Name of url miss');
                    $name = (string) $url->name;
                    if (empty($name) || !Validate::isVariableName($name))
                        throw new \Exception('Name of url must be as non empty string and validate variable name');
                    if (array_key_exists($name, self::$_urls))
                        Logger::getInstance()->debug('Url : "' . $name . '" already load, was overloaded');

                    $isRealUrl = isset($url->value) ? true : false;
                    if ($isRealUrl) {
                        $value = '';
                        if (isset($url->value['includeUrl']))
                            $value .= self::getUrl($url->value['includeUrl']);
                        $value .= Tools::castValue((string) $url->value);
                        $controller = null;
                        $action = null;
                        $file = null;
                        $rewrite = isset($url->rewrite) ? Tools::castValue((string) $url->rewrite) : null;
                        $ssl = isset($url->ssl) ? Tools::castValue((string) $url->ssl) : null;
                    } else {
                        $value = null;
                        if (!isset($url->controller))
                            throw new \Exception('Controller of url miss');
                        $controller = (string) $url->controller;
                        $action = !empty($url->action) ? (string) $url->action : null;
                        $file = isset($url->file) ? Tools::castValue((string) $url->file) : null;
                        $rewrite = isset($url->rewrite) ? Tools::castValue((string) $url->rewrite) : null;
                        $ssl = isset($url->ssl) ? Tools::castValue((string) $url->ssl) : null;
                    }

                    // Set url
                    self::$_urls->$name = array(
                        'value' => $value,
                        'file' => $file,
                        'controller' => $controller,
                        'action' => $action,
                        'ssl' => $ssl,
                        'rewrite' => $rewrite);
                }
                break;
            case self::INI:
                throw new \Exception('NOT YET');
                break;
            default:
                throw new \Exception('Config format must be setted');
                break;
        }
    }

    public static function _setSslUrl($urlValue, $sslValue) {
        if (!Http::isHttps()) {
            if ($sslValue && !Validate::isUrl($urlValue, true))
                return str_replace('http://', 'https://', $urlValue);
            else
                return str_replace('https://', 'http://', $urlValue);
        }
        else
            return str_replace('http://', 'https://', $urlValue);
    }

    public static function _generateUrlValue($url, $urlName) {
        $urlManager = !is_null($url['file']) ? new UrlManager($url['file']) : new UrlManager();
        if (!is_null($url['controller']))
            $urlManager->addArg('controller', $url['controller']);
        if (!is_null($url['controller']) && !is_null($url['action']))
            $urlManager->addArg('action', $url['action']);
        if ($url['rewrite'] || (defined('REWRITE_URLS') && REWRITE_URLS))
            $urlManager->setRewrite(true, false);
        $value = $urlManager->getUrl(false, true, true);


        return (isset($url['ssl']) || Http::isHttps()) ? self::_setSslUrl($value, $url['ssl']) : $value;
    }

}

?>