<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\mvc\Router;
use framework\mvc\router\Route as RouterRoute;
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

            // create instance of route 
            $route = new RouterRoute($name, $datas['controller']);

            // Optionnals parameters
            if (isset($datas['regex']))
                $route->setRegex(Tools::castValue($datas['regex']));

            if (isset($datas['requireSsl']))
                $route->setRequireSsl(Tools::castValue($datas['requireSsl']));

            if (isset($datas['requireAjax']))
                $route->setRequireAjax(Tools::castValue($datas['requireAjax']));

            if (isset($datas['autoSetAjax']))
                $route->setAutoSetAjax(Tools::castValue($datas['autoSetAjax']));

            if (isset($datas['httpResponseStatusCode']))
                $route->setHttpResponseStatusCode(Tools::castValue($datas['httpResponseStatusCode']));

            if (isset($datas['httpProtocol']))
                $route->setHttpProtocol(Tools::castValue($datas['httpProtocol']));



            if (isset($datas['requireHttpMethods'])) {
                if (is_array($datas['requireHttpMethods'])) {
                    if (isset($datas['requireHttpMethods']['requireHttpMethod']) && is_array($datas['requireHttpMethods']['requireHttpMethod']))
                        $datas['requireHttpMethods'] = $datas['requireHttpMethods']['requireHttpMethod'];

                    $route->setRequireHttpMethods($datas['requireHttpMethods']);
                } else
                    $route->setRequireHttpMethods(array($datas['requireHttpMethods']));
            }
            if (isset($datas['security'])) {
                if (is_array($datas['security'])) {
                    if (isset($datas['security']['security']) && is_array($datas['security']['security']))
                        $datas['security'] = $datas['security']['security'];

                    $route->setSecurity($datas['security']);
                } else
                    $route->setSecurity(array($datas['security']));
            }

            if (isset($datas['rules'])) {
                if (is_array($datas['rules'])) {
                    if (isset($datas['rules']['rule']) && is_array($datas['rules']['rule']))
                        $datas['rules'] = $datas['rules']['rule'];

                    $route->setRules($datas['rules']);
                } else
                    $route->setRules(array($datas['rules']));
            }


            if (isset($datas['actions'])) {
                if (is_array($datas['actions'])) {
                    if (isset($datas['actions']['action']) && is_array($datas['actions']['action'])) {
                        $datas['actions'] = $datas['actions']['action'];
                    }
                    $actions = $datas['actions'];
                } else
                    $actions = array($datas['actions']);

                $actionsCast = array();
                foreach ($actions as $action => $parameters) {
                    if (is_int($action) && is_string($parameters)) {
                        $actionsCast[$parameters] = array();
                    } elseif (is_int($action) && is_array($parameters)) {
                        //xml
                        if (isset($parameters['@attributes']) && isset($parameters['@attributes']['name'])) {
                            $name = $parameters['@attributes']['name'];
                            $params = array();
                            if (isset($parameters['parameter'])) {
                                if (is_array($parameters['parameter']))
                                    $params = $parameters['parameter'];
                                elseif (is_string($parameters['parameter']))
                                    $params = array($parameters['parameter']);
                            }
                            $actionsCast[$name] = $params;
                        }
                    } elseif (is_string($action) && is_array($parameters)) {
                        $actionsCast[$action] = $parameters;
                    }
                }

                $route->setActions($actionsCast);
            }

            // Add into router
            Router::addRoute($route, true);
        }
    }

}

?>