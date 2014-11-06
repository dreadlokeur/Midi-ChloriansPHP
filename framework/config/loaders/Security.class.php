<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\utility\Tools;
use framework\Security as SecurityManager;
use framework\utility\Validate;

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
                }
            }


            $params['name'] = $name;

            // Add
            SecurityManager::addSecurity($name, SecurityManager::factory($datas['adaptater'], $params, 'framework\security\adaptaters', 'framework\security\IAdaptater'), true);
        }
    }

}

?>
