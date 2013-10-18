<?php

namespace framework;

use framework\security\Api;

class Security {

    protected static $_registeredApi = array();

    public static function registerApis($apis) {
        foreach ($apis as $apiType => $apiOptions)
            self::registerApi($apiType, $apiOptions['datas']);
    }

    public static function registerApi($apiType, $apiOptions = array(), $forceReplace = false) {
        if (!Api::isValidApiType($apiType))
            throw new \Exception('Invalid api type');

        if (Api::getDebug())
            Logger::getInstance()->debug('Trying register security API : "' . $apiType . '"', 'securityApi');
        if (!self::isRegisteredApi($apiType) && !$forceReplace) {
            self::$_registeredApi[$apiType] = Api::factory($apiType, $apiOptions);
            if (Api::getDebug())
                Logger::getInstance()->debug('Security API : "' . $apiType . '" registered', 'securityApi');
            if (isset($apiOptions['autorun']) && $apiOptions['autorun'])
                self::runApi($apiType, false);
        }else {
            if (Api::getDebug())
                Logger::getInstance()->debug('Trying register security API : "' . $apiType . '" already registered, was overloaded', 'securityApi');
        }
    }

    public static function runApis() {
        foreach (self::$_registeredApi as $api)
            $api->run();
    }

    public static function runApi($apiType, $check = true) {
        if (!$check) {
            self::$_registeredApi[$apiType]->run();
            return;
        }
        if (self::isRegisteredApi($apiType))
            self::$_registeredApi[$apiType]->run();
    }

    public static function isRegisteredApi($apiType) {
        if (!is_string($apiType) && !is_int($apiType))
            throw new \Exception('Api type must be string or integer');

        return array_key_exists($apiType, self::$_registeredApi);
    }

    public static function getApi($apiType) {
        if (self::isRegisteredApi($apiType))
            return self::$_registeredApi[$apiType];
        else {
            Logger::getInstance()->debug('Trying get unregistered security API');
            return false;
        }
    }

}

?>
