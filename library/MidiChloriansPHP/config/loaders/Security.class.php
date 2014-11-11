<?php

namespace MidiChloriansPHP\config\loaders;

use MidiChloriansPHP\config\Loader;
use MidiChloriansPHP\config\Reader;
use MidiChloriansPHP\utility\Tools;
use MidiChloriansPHP\Security as SecurityManager;
use MidiChloriansPHP\utility\Validate;

class Security extends Loader {

    public function load(Reader $reader) {
        $security = $reader->read();
        foreach ($security as $name => $datas) {
            if (!Validate::isVariableName($name))
                throw new \Exception('Name of template must be a valid variable name');

            //check required keys
            if (!isset($datas['adaptater']))
                throw new \Exception('Miss adaptater config param for security : "' . $name . '"');


            // Cast global setting
            $params = array();
            foreach ($datas as $key => $value) {
                if ($key == 'comment')
                    continue;

                // Casting
                if (is_string($value))
                    $value = Tools::castValue($value);
                $params[$key] = $value;
            }

            if (isset($datas['urlsReferer'])) {
                if (is_array($datas['urlsReferer'])) {
                    if (isset($datas['urlsReferer']['urlReferer']) && is_array($datas['urlsReferer']['urlReferer']))
                        $params['urlsReferer'] = $datas['urlsReferer']['urlReferer'];
                    else
                        $params['urlsReferer'] = $datas['urlsReferer'];
                } else
                    $params['urlsReferer'] = array($datas['urlsReferer']);
            }

            if (isset($datas['httpMethods'])) {
                if (is_array($datas['httpMethods'])) {
                    if (isset($datas['httpMethods']['httpMethods']) && is_array($datas['httpMethods']['httpMethod']))
                        $params['httpMethods'] = $datas['httpMethods']['httpMethod'];
                    else
                        $params['httpMethods'] = $datas['httpMethods'];
                } else
                    $params['httpMethods'] = array($datas['httpMethods']);
            }


            $params['name'] = $name;

            // Add
            SecurityManager::addSecurity($name, SecurityManager::factory($datas['adaptater'], $params, 'MidiChloriansPHP\security\adaptaters', 'MidiChloriansPHP\security\IAdaptater'), true);
        }
    }

}

?>
