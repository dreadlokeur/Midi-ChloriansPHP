<?php

namespace framework\security;

use framework\Logger;

class Api {

    use \framework\debugger\Debug;

    const TYPE_CRYPTION = 'cryption';
    const TYPE_FORM = 'form';
    const TYPE_SNIFFER = 'sniffer';

    public static function setDebug($debug) {
        self::$_debug = $debug;
        if (self::$_debug)
            Logger::getInstance()->addGroup('securityApi', 'Security API report', true);
    }

    public static function isValidApiType($type) {
        return (is_string($type) && $type == self::TYPE_CRYPTION || $type == self::TYPE_FORM || $type == self::TYPE_SNIFFER);
    }

    public static function factory($apiType, $apiOptions = array()) {
        if (class_exists('framework\security\api\\' . ucfirst($apiType)))
            $class = 'framework\security\api\\' . ucfirst($apiType);
        else
            $class = $apiType;

        $classInstance = new \ReflectionClass($class);
        if (!in_array('framework\security\IApi', $classInstance->getInterfaceNames()))
            throw new \Exception('API class must be implement framework\security\IApi');
        if ($classInstance->isAbstract())
            throw new \Exception('API class must be not abstract class');
        if ($classInstance->isInterface())
            throw new \Exception('API class must be not interface');

        return $classInstance->newInstance($apiOptions);
    }

}

?>
