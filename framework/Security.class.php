<?php

namespace framework;

use framework\Logger;
use framework\security\IAdaptater;

class Security {

    use \framework\pattern\Factory;

    protected static $_security = array();

    public static function addSecurity($name, IAdaptater $security, $forceReplace = false) {
        if (array_key_exists($name, self::$_security)) {
            if (!$forceReplace)
                throw new \Exception('Security : "' . $name . '" already defined');

            Logger::getInstance()->debug('Security : "' . $name . '" already defined, was overloaded');
        }

        self::$_security[$name] = $security;
    }

    public static function getSecurity($name = null) {
        if (is_null($name))
            return self::$_security;

        if (!is_string($name))
            throw new \Exception('Security name must be a string');

        if (!array_key_exists($name, self::$_security))
            return false;

        return self::$_security[$name];
    }

    public static function autorun() {
        foreach (self::$_security as &$security) {
            if ($security->getAutorun())
                $security->run();
        }
    }

}

?>
