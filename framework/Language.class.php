<?php

//TODO set cookie when update language with setLanguage, if !isDefault
namespace framework;

use framework\utility\Validate;
use framework\utility\Tools;

class Language {

    use pattern\Singleton,
        debugger\Debug,
        cache\Cache;

    protected $_acceptedList = array();
    protected $_datasPath = null;
    protected $_language = null;
    protected static $_languageVars = null;
    protected $_defaultLanguageVars = array();

    public static function getVar($varName) {
        if (!property_exists(self::$_languageVars, $varName)) {
            Logger::getInstance()->debug('Language var ' . $varName . ' is not setted');
            return null;
        }
        else
            return self::$_languageVars->$varName;
    }

    public static function getVars() {
        return self::$_languageVars;
    }

    //langs list and path vars datas
    public function setLangs($acceptedList, $datasPath, $defaultLanguage) {
        if (!is_array($acceptedList) && !is_string($acceptedList))
            throw new \Exception('acceptedList must be an array or a string');
        if (is_string($acceptedList))
            $acceptedList = explode(',', $acceptedList);
        foreach ($acceptedList as $language) {
            if (!Validate::isLanguage($language))
                throw new \Exception('Invalid lang : "' . $language . '"');
        }
        $this->_acceptedList = $acceptedList;

        if (!is_dir($datasPath))
            throw new \Exception('Directory "' . $datasPath . '" do not exists');
        if (!is_readable($datasPath))
            throw new \Exception('Directory "' . $datasPath . '" is not readable');

        $this->_datasPath = realpath($datasPath) . DS;
        //TODO check if contains alls langs ?

        $this->setLanguage($defaultLanguage, true, true);
    }

    public function getAcceptedList() {
        return $this->_acceptedList;
    }

    public function getDatasPath() {
        return $this->_datasPath;
    }

    public function setLanguage($language, $loadVars = true, $isDefault = false) {
        if (!Validate::isLanguage($language))
            throw new \Exception('Invalid lang format');
        //check if is accepted language
        if (!in_array($language, $this->_acceptedList))
            throw new \Exception('Invalid lang: "' . $language . '", is not accepted language');
        $this->_language = $language;

        // load vars
        if ($loadVars)
            $this->_loadVars($isDefault);
    }

    public function getLanguage() {
        return $this->_language;
    }

    protected function _loadVars($isLanguageDefault = false) {
        $xml = @simplexml_load_file($this->getDatasPath() . $this->getLanguage() . '.xml');
        if ($xml === null || $xml === false)
            throw new \Exception('Invalid xml file');


        if (!$isLanguageDefault)//preserve defaults vars
            $this->_defaultLanguageVars = self::$_languageVars;
        //reset vars
        self::$_languageVars = new \stdClass();
        foreach ($xml as $varName => $varValue) {
            //cast value
            $varValue = Tools::castValue((string) $varValue);
            if (method_exists(self::$_languageVars, $varName))
                throw new \Exception('language var already defined');

            //put on vars
            self::$_languageVars->$varName = $varValue;
        }
        //Check if alls vars defined
        if (!$isLanguageDefault) {
            foreach ($this->_defaultLanguageVars as $name => $value) {
                if (!property_exists(self::$_languageVars, $name)) {
                    // Notify
                    Logger::getInstance()->debug('Miss language var : "' . $name . '" on new language file : "' . $this->_filename . '"');
                    // And add default language var
                    self::$_languageVars->$name = $this->_defaultLanguageVars->$name;
                }
            }
        }
    }

}

?>
