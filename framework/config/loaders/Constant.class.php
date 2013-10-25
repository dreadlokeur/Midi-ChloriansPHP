<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\Logger;

class Constant extends Loader {

    protected static $_constants = array();

    public function load(Reader $reader) {
        $constants = $reader->read();
        foreach ($constants as $name => $value) {
            if (empty($name) || !is_string($name))
                throw new \Exception('Name of constant must be as non empty string');
            if (!Validate::isVariableName($name))
                throw new \Exception('Name of constant must be a valid variable name');

            if (array_key_exists($name, self::$_constants)) {
                if (defined($name)) {
                    Logger::getInstance()->debug('Constant : "' . $name . '" already defined');
                    continue;
                }
                Logger::getInstance()->debug('Constant : "' . $name . '" already load, was overloaded');
            }
            // casting
            if (is_string($value))
                $value = Tools::castValue($value);

            self::$_constants[$name] = $value;
        }
    }

    public static function defineCons() {
        foreach (self::$_constants as $name => $value) {
            if (defined($name)) {
                Logger::getInstance()->debug('Constant ' . $name . ' already defined');
                continue;
            }

            define($name, $value);
        }
    }

    public static function getCons() {
        return self::$_constants;
    }

}

?>
