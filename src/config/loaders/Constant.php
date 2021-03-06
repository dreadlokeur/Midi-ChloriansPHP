<?php

namespace MidiChloriansPHP\config\loaders;

use MidiChloriansPHP\config\Loader;
use MidiChloriansPHP\config\Reader;
use MidiChloriansPHP\utility\Validate;
use MidiChloriansPHP\utility\Tools;
use MidiChloriansPHP\Logger;

class Constant extends Loader {

    protected static $_constants = array();

    public function load(Reader $reader) {
        $constants = $reader->read();
        foreach ($constants as $name => $value) {
            // Check name
            if (!Validate::isVariableName($name))
                throw new \Exception('Name of constant must be a valid variable name');

            // Check if is already loaded
            if (array_key_exists($name, self::$_constants)) {
                // If is already defined => next
                if (defined($name)) {
                    Logger::getInstance()->debug('Constant : "' . $name . '" already defined');
                    continue;
                }
                Logger::getInstance()->debug('Constant : "' . $name . '" already load, was overloaded');
            }
            if (is_array($value)) {
                if (empty($value))
                    $value = null;
                else
                    throw new \Exception('Constant value cannot be an array');
            }

            // Cast value
            if (is_string($value))
                $value = Tools::castValue($value);

            // Add
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
