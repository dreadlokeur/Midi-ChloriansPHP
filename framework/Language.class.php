<?php

namespace framework;

use framework\utility\Validate;
use framework\Logger;

class Language {

    use pattern\Singleton;

    protected $_language = null;
    protected $_defaultLanguage = null;
    protected static $_datasPath = null;
    protected static $_languageVars = null;
    protected static $_defaultLanguageVars = null;

    public static function getVar($varName, $default = null) {
        if (!property_exists(self::$_languageVars, $varName)) {
            Logger::getInstance()->debug('Language var ' . $varName . ' is not setted');
            return $default;
        }
        else
            return self::$_languageVars->$varName;
    }

    public static function getVars() {
        return self::$_languageVars;
    }

    public static function setVar($name, $value, $forceReplace = false) {
        if (!Validate::isVariableName($name))
            throw new \Exception('language var name must be a valid variable');

        if (method_exists(self::$_languageVars, $name) && !$forceReplace)
            throw new \Exception('language var already defined');

        //put on vars
        self::$_languageVars->$name = $value;
    }

    public function __set($name, $value) {
        return self::setVar($name, $value);
    }

    public function __get($name) {
        return self::getVar($name);
    }

    protected function __construct() {
        Logger::getInstance()->addGroup('language', 'Language informations', false, true);
    }

    public function __destruct() {
        //if ($this->_defaultLanguage)
        //    Logger::getInstance()->debug('Language default is : "' . $this->_defaultLanguage . '"', 'language');

        //Logger::getInstance()->debug(count((array) self::$_languageVars) . ' vars defined', 'language');
    }

    public static function setDatasPath($datasPath) {
        if (!is_dir($datasPath))
            throw new \Exception('Directory "' . $datasPath . '" do not exists');
        if (!is_readable($datasPath))
            throw new \Exception('Directory "' . $datasPath . '" is not readable');

        self::$_datasPath = realpath($datasPath) . DS;
    }

    public static function getDatasPath() {
        return self::$_datasPath;
    }

    public function setLanguage($language, $setAsDefault = false) {
        if (!Validate::isLanguage($language))
            throw new \Exception('Invalid lang format');

        Logger::getInstance()->debug('Try load language : "' . $language . '"', 'language');
        //check datas files
        $file = self::getDatasPath() . $language . '.xml';
        if (!file_exists($file))
            throw new \Exception('Invalid lang : "' . $language . '", have not xml datas file');
        self::$_languageVars = @simplexml_load_file($file);
        if (self::$_languageVars === null || self::$_languageVars === false)
            throw new \Exception('Invalid lang : "' . $language . '" invalid xml file');

        Logger::getInstance()->debug('Load datas file : "' . $file . '"', 'language');

        $this->_language = $language;
        if ($setAsDefault)
            $this->_defaultLanguage = $this->_language;


        //load datas
        $isDefault = ($this->_defaultLanguage != $this->_language);
        if (!$isDefault)//preserve defaults vars
            self::$_defaultLanguageVars = self::$_languageVars;

        //Check if alls vars defined
        if (!$isDefault && self::$_defaultLanguageVars !== null) {
            foreach (self::$_defaultLanguageVars as $name => $value) {
                if (!property_exists(self::$_languageVars, $name)) {
                    // Notify
                    Logger::getInstance()->debug('Miss language var : "' . $name . '" on new language : "' . $language . '"');
                    // And add default language var
                    self::$_languageVars->$name = self::$_defaultLanguageVars->$name;
                }
            }
        }

        Logger::getInstance()->debug('Current language is : "' . $this->_language . '"', 'language');
    }

    public function getLanguage() {
        return $this->_language;
    }

}

?>
