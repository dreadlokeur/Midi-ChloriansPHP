<?php

namespace framework\mvc;

use framework\utility\Validate;
use framework\utility\Tools;
use framework\Config;
use framework\minify\Css;
use framework\minify\Javascript;
use framework\network\Http;
use framework\Cli;
use framework\Language;

class Template {

    const ASSET_TYPE_URL = 'url';
    const ASSET_TYPE_PATH = 'path';
    const ASSET_TYPE_CACHE = 'cache';
    const ASSET_TYPE_FULL = 'full';

    protected static $_templates = array();
    protected static $_template = null;
    protected $_conf = null;
    protected $_vars = null;
    protected $_charset = null;
    protected $_file = null;

    public static function setTemplate($templateName) {
        if (!is_string($templateName))
            throw new \Exception('Template name must be a string');
        if (!array_key_exists($templateName, self::$_templates))
            throw new \Exception('Trying to set template, but config isn\'t setted');

        self::$_template = $templateName;
    }

    public static function getTemplate($templateName = null) {
        $templateName = is_null($templateName) ? self::$_template : $templateName;
        if (empty($templateName))
            return false;
        if (array_key_exists($templateName, self::$_templates))
            return self::$_templates[$templateName];
        else {
            Logger::getInstance()->debug('Try getting unregistered template name : "' . $templateName . '"');
            return null;
        }
    }

    public static function addTemplate($name, $conf, $forceReplace = false) {
        if (array_key_exists($name, Template::getTemplates())) {
            if ($forceReplace)
                throw new \Exception('Template : "' . $name . '" already defined');

            Logger::getInstance()->debug('Template : "' . $name . '" already defined, was overloaded');
        }
        self::$_templates[$name] = $conf;
    }

    public static function getTemplates() {
        return self::$_templates;
    }

    public function __construct($config) {
        $this->_conf = $config;
        // init variable template, for use into tpl file ...
        $this->_vars = new \stdClass();
        $template = new \stdClass();
        $template->name = $config->name;
        $template->path = $config->path;

        // Parse assets urls, for add root assets url
        foreach ($config->assets as $name => $datas) {
            if (is_array($datas) && isset($datas['url'])) {
                $asset = $config->assets->$name;
                $asset['url'] = $this->getAssetsRootUrl($config->assets->rootDirectory) . $datas['url'];
                // Update
                $config->assets->$name = $asset;
            }
        }
        $template->assets = $config->assets;

        if (!Validate::isCharset($config->charset))
            throw new \Exception('Charset in\'t a valid charset type');
        $template->charset = $config->charset;

        // Set langs/urls vars into tpl
        $this->set('template', $template)
                ->set('urls', Config::getUrls(true))
                ->set('langs', Language::getVars())
                ->set('lang', Language::getInstance()->getLanguage()
        );

        // Template assets autoloading, cache and compress
        if (!Cli::isCli() && !Http::isAjaxRequest()) {
            // Set assets urls ssl
            if (Http::isHttps())
                $this->setSslUrlAssets(true);

            // Css minifier and cache
            $cssAssetInfos = $this->getAsset('css', self::ASSET_TYPE_FULL);
            if (!empty($cssAssetInfos) && isset($cssAssetInfos['cache']) && !empty($cssAssetInfos['cache'])) {
                $css = new Css($cssAssetInfos['path']);
                if (isset($cssAssetInfos['cache']['path']))
                    $css->setCacheDir($cssAssetInfos['cache']['path']);
                if (isset($cssAssetInfos['cache']['fileName']))
                    $css->setCacheName($cssAssetInfos['cache']['fileName']);
                if (isset($cssAssetInfos['cache']['fileMeta']))
                    $css->setCacheHashName($cssAssetInfos['cache']['fileMeta']);
                if (isset($cssAssetInfos['cache']['compress']))
                    $css->setCompressed($cssAssetInfos['cache']['compress']);
                // autoloading css files
                foreach (Tools::cleanScandir($css->getCssDir()) as $value) {
                    if (Validate::isFileType('css', $value) && $value != $css->getCacheName())
                        $css->addFile($this->getAsset('css') . $value);
                }
                // Finally, generate global cache
                $css->output();
                $this->template->assets->css['cacheName'] = $this->getAssetsRootUrl() . $cssAssetInfos['cache']['url'] . $css->getCacheName();
                $this->template->assets->css['cacheName'] .= '?' . filemtime($cssAssetInfos['cache']['path'] . $css->getCacheName());
            }
            else
                $this->template->assets->css['cacheName'] = false;


            // Js minifier and cache
            $jsAssetInfos = $this->getAsset('js', self::ASSET_TYPE_FULL);
            if (!empty($jsAssetInfos) && isset($jsAssetInfos['cache']) && !empty($jsAssetInfos['cache'])) {
                $js = new Javascript(true);
                if (isset($jsAssetInfos['cache']['path']))
                    $js->setCacheDir($jsAssetInfos['cache']['path']);
                if (isset($jsAssetInfos['cache']['fileName']))
                    $js->setCacheName($jsAssetInfos['cache']['fileName']);
                if (isset($jsAssetInfos['cache']['fileMeta']))
                    $js->setCacheHashName($jsAssetInfos['cache']['fileMeta']);
                if (isset($jsAssetInfos['cache']['compress']))
                    $js->setCompressed($jsAssetInfos['cache']['compress']);
                // autoloading javascript files
                foreach (Tools::cleanScandir($this->getAsset('js')) as $value) {
                    if (Validate::isFileType('js', $value) && $value != $js->getCacheName())
                        $js->addFile($this->getAsset('js') . $value, false);
                }
                // Finally, generate global cache
                $js->output();

                $this->template->assets->js['cacheName'] = $this->getAssetsRootUrl() . $jsAssetInfos['cache']['url'] . $js->getCacheName();
                $this->template->assets->js['cacheName'] .= '?' . filemtime($jsAssetInfos['cache']['path'] . $js->getCacheName());
            }
            else
                $this->template->assets->js['cacheName'] = false;


            if ($jsAssetInfos['urls'] && $jsAssetInfos['urls']) {
                $jsInjection = 'var urls = {};';
                foreach ($this->urls as $urlName => $urlValue)
                    $jsInjection .= 'urls[\'' . $urlName . '\'] = \'' . $urlValue . '\';';

                //add img, css, js etc url...
                $jsInjection .= 'urls[\'img\'] = \'' . $this->getAsset('img', self::ASSET_TYPE_URL) . '\';';
                $jsInjection .= 'urls[\'css\'] = \'' . $this->getAsset('css', self::ASSET_TYPE_URL) . '\';';
                $jsInjection .= 'urls[\'js\'] = \'' . $this->getAsset('js', self::ASSET_TYPE_URL) . '\';';
                $jsInjection .= 'urls[\'font\'] = \'' . $this->getAsset('font', self::ASSET_TYPE_URL) . '\';';
                $jsInjection .= 'urls[\'sound\'] = \'' . $this->getAsset('sound', self::ASSET_TYPE_URL) . '\';';
                $this->template->assets->js['urls'] = $jsInjection;
            }
            if ($jsAssetInfos['langs'] && $jsAssetInfos['langs']) {
                $jsInjection = 'var langs = {};';
                if (!empty($this->langs)) {
                    foreach ($this->langs as $langName => $langValue)
                        $jsInjection .= 'langs[\'' . $langName . '\'] = \'' . $langValue . '\';';
                    $this->template->assets->js['langs'] = $jsInjection;
                }
            }
        }
    }

    public function getName() {
        return $this->_conf->name;
    }

    public function getPath() {
        return $this->_conf->path;
    }

    public function getCharset() {
        return $this->_conf->charset;
    }

    public function setSslUrlAssets($ssl = true) {
        if (!is_bool($ssl))
            throw new \Exception('Ssl parameter must be a boolean');

        $assets = new \stdClass();
        foreach ($this->_conf->assets as $assetName => $asset) {
            $assetContent = array();
            foreach ($asset as $type => $value) {
                if ($type == self::ASSET_TYPE_URL)
                    $value = ($ssl) ? str_replace('http://', 'https://', $value) : str_replace('https://', 'http://', $value);

                $assetContent[$type] = $value;
            }
            $assets->$assetName = $assetContent;
        }
        $template = new \stdClass();
        $template->name = $this->_conf->name;
        $template->path = $this->_conf->path;
        $template->assets = $assets;
        $this->set('template', $template, false, true);
    }

    public function getAsset($assetName, $assetType = self::ASSET_TYPE_PATH) {
        if (!property_exists($this->_conf->assets, $assetName))
            throw new \Exception('Trying to get undefined asset');
        switch ($assetType) {
            case self::ASSET_TYPE_PATH:
            case self::ASSET_TYPE_URL:
            case self::ASSET_TYPE_CACHE:
            case self::ASSET_TYPE_FULL:
                break;
            default:
                throw new \Exception('Invalid asset type argument');
                break;
        }

        if ($assetType != self::ASSET_TYPE_FULL) {
            foreach ($this->_conf->assets->$assetName as $type => $assetTypeValue) {
                if ($type == $assetType)
                    return ($assetType == self::ASSET_TYPE_URL) ? $this->getAssetsRootUrl() . $assetTypeValue : $assetTypeValue;
            }
        }
        else
            return $this->_conf->assets->$assetName;
    }

    public function getAssetsRootUrl($rootDirectory = null) {
        $rootDirectory = is_null($rootDirectory) ? $this->template->assets->rootDirectory : $rootDirectory;
        return Config::getUrl('root') . str_replace('\\', '/', str_replace(PATH_ROOT, '', $rootDirectory));
    }

    public function __get($name) {
        if (property_exists($this->_vars, $name))
            return $this->_vars->$name;
        return null;
    }

    public function set($name, $value, $safeValue = false, $forceReplace = false) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Name of variable must be a valid variable name');
        if (!$forceReplace && property_exists($this->_vars, $name))
            throw new \Exception('Variable "' . $name . '" already defined in template');
        $this->_vars->{$name} = $safeValue ? $value : $this->_sanitize($value);
        return $this;
    }

    public function merge($vars, $safeValue = false, $forceReplace = false) {
        $table = $vars;
        if (is_object($table))
            $table = (array) $table;
        if (!is_array($vars))
            throw new \Exception('Parameter for merge must be an array or an object');
        foreach ($table as $key => &$value)
            $this->set($key, $value, $safeValue, $forceReplace);
        return $this;
    }

    public function unsetVar($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Name of variable must be a valid variable name');
        if (!property_exists($this->_vars, $name))
            throw new \Exception('Variable : "' . $name . '" don\'t exists');
        unset($this->_vars->{$name});
    }

    public function unsetVars() {
        $this->_vars = new \stdClass();
    }

    public function setTemplateFile($file) {
        if (!is_readable($this->_conf->path . $file))
            throw new \Exception('Template : "' . $this->_conf->path . $file . '" isn\'t a readable file or no exists');
        $this->_file = $file;

        return $this;
    }

    public function getTemplateFile() {
        return $this->_file;
    }

    public function displayTemplate() {
        if (is_null($this->_file))
            return false;

        return include($this->_conf->path . $this->_file);
    }

    protected function _sanitize($value) {
        if (is_array($value)) {
            foreach ($value as &$v)
                $v = $this->_sanitize($v);
        } elseif (is_object($value)) {
            $reflexion = new \ReflectionObject($value);
            $properties = $reflexion->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($properties as &$propertie)
                $value->{$propertie->name} = $this->_sanitize($value->{$propertie->name});
        } elseif (is_string($value))
            $value = htmlspecialchars(htmlspecialchars_decode($value, ENT_QUOTES), ENT_QUOTES, $this->_charset);

        return $value;
    }

    public function getTemplateFileContents($file = false) {
        $file = (!$file) ? $this->_file : $file;
        if (!is_file($this->_conf->path . $file) && !is_readable($this->_conf->path . $file))
            throw new \Exception('Template contents invalid, not readable file setted');
        ob_start();
        require $this->_conf->path . $file;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

}

?>