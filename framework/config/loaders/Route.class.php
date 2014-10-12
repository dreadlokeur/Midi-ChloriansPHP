<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\mvc\Router;
use framework\utility\Validate;
use framework\utility\Tools;

class Route extends Loader {

    public function load(Reader $reader) {
        $routes = $reader->read();
        foreach ($routes as $name => $datas) {
            // Check name
            if (!Validate::isVariableName($name))
                throw new \Exception('Route name must be a valid variable');

            // Check controller info
            if (!isset($datas['controller']))
                throw new \Exception('Miss controller into route "' . $name . '"');

            // Check optionnal parameters
            $regex = isset($datas['regex']) ? is_string($datas['regex']) ? Tools::castValue($datas['regex']) : $datas['regex'] : false;
            $requireSsl = isset($datas['requireSsl']) ? is_string($datas['requireSsl']) ? Tools::castValue($datas['requireSsl']) : $datas['requireSsl'] : false;
            $requireAjax = isset($datas['requireAjax']) ? is_string($datas['requireAjax']) ? Tools::castValue($datas['requireAjax']) : $datas['requireAjax'] : false;
            $autoSetAjax = isset($datas['autoSetAjax']) ? is_string($datas['autoSetAjax']) ? Tools::castValue($datas['autoSetAjax']) : $datas['autoSetAjax'] : true;
            $requireHttpMethod = isset($datas['requireHttpMethod']) ? is_string($datas['requireHttpMethod']) ? Tools::castValue($datas['requireHttpMethod']) : $datas['requireHttpMethod'] : null;
            $rules = isset($datas['rules']) ? $datas['rules'] : array();
            if (isset($rules['rule']) && is_array($rules['rule']))
                $rules = $rules['rule'];

            // Check methods
            $methods = isset($datas['methods']) ? $datas['methods'] : array();
            foreach ($methods as $method => $val) {
                //no have parameters, replace wtih empty parameters list
                if (is_int($method)) {
                    //TODO fix : replace methode into good order
                    unset($methods[$method]);
                    $methods[$val] = array();
                }
            }


            // Add into router
            Router::addRoute($name, $datas['controller'], $rules, $methods, $requireSsl, $regex, $requireAjax, $autoSetAjax, $requireHttpMethod, true);
        }
    }

}

?>