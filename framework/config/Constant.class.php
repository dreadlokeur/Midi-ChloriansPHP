<?php

namespace framework\config;

use framework\Config;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\Logger;

class Constant extends Config {

    const TYPE_INTEGER = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'bool';
    const TYPE_NULL = 'null';

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
    }

    public function load($defineCons = true) {
        switch ($this->_format) {
            case self::XML:
                $xml = @simplexml_load_file($this->_filename);
                if ($xml === null || $xml === false)
                    throw new \Exception('Invalid xml file');
                $constants = $xml->constant;
                foreach ($constants as $constant) {
                    if (!isset($constant->name))
                        throw new \Exception('Name of constant miss');
                    $name = (string) $constant->name;
                    if (array_key_exists($name, self::$_constants)) {
                        if (!defined($name))
                            Logger::getInstance()->debug('Constant : "' . $name . '" already load, was overloaded');
                        else {
                            Logger::getInstance()->debug('Constant ' . $name . ' already defined');
                            continue;
                        }
                    }

                    if (!isset($constant->value))
                        throw new \Exception('Value of constant miss');

                    // cast simpleXmlObject
                    $value = (string) $constant->value;
                    // Constant has a specific type
                    if (isset($constant->value['type'])) {
                        $type = (string) $constant->value['type'];
                        switch ($type) {
                            case self::TYPE_INTEGER:
                                $value = (int) $value;
                                break;
                            case self::TYPE_FLOAT:
                                $value = (float) $value;
                                break;
                            case self::TYPE_STRING:
                                $value = (string) $value;
                                break;
                            case self::TYPE_BOOLEAN:
                                $value = (bool) $value;
                                break;
                            case self::TYPE_NULL:
                                $value = null;
                                break;
                            case self::TYPE_OBJECT:
                                $value = (object) $value;
                                break;
                            default:
                                throw new \Exception('Invalid type constant value');
                                break;
                        }
                        $isCasted = true;
                    }
                    $value = isset($isCasted) && $isCasted ? $value : Tools::castValue($value);
                    if (empty($name) || !is_string($name))
                        throw new \Exception('Name of constant must be as non empty string');
                    if (!Validate::isVariableName($name))
                        throw new \Exception('Name must be a valid variable name');


                    self::$_constants[$name] = $value;
                }
                break;
            case self::INI:
                throw new \Exception('NOT YET');
                break;
            default:
                throw new \Exception('Config format must be setted');
                break;
        }
        if ($defineCons)
            $this->defineCons();
    }

    public static function defineCons() {
        foreach (self::$_constants as $name => $value) {
            if (defined($name))
                Logger::getInstance()->debug('Constant ' . $name . ' already defined');
            else
                define($name, $value);
        }
    }

}

?>