<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\utility\Tools;
use framework\mvc\Template as TemplateManager;

class Template extends Loader {

    public function load(Reader $reader) {
        $templates = $reader->read();
        foreach ($templates as $name => $datas) {
            //check required keys
            if (!isset($datas['path']))
                throw new \Exception('Miss path config param for template : "' . $name . '"');
            if (!isset($datas['class']))
                throw new \Exception('Miss class config param for template : "' . $name . '"');


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
            $params['name'] = $name;


            // foreach assets for checking parameters and casting
            if (isset($params['assets']) && is_array($params['assets'])) {
                foreach ($params['assets'] as $assetType => $assetDatas) {
                    //check type
                    if (!TemplateManager::isValidAssetType($assetType))
                        throw new \Exception('Invalid asset : "' . $assetType . '"');


                    if (is_array($assetDatas)) {
                        foreach ($assetDatas as $d => $v) {
                            // Casting
                            if (is_string($v))
                                $params['assets'][$assetType][$d] = Tools::castValue($v);

                            // cache parameters
                            if (isset($assetDatas['cache']) && is_array($assetDatas['cache'])) {
                                if (!isset($assetDatas['cache']['name']))
                                    throw new \Exception('Miss cache name');

                                foreach ($assetDatas['cache'] as $cacheOption => $optionsValue) {
                                    // Casting
                                    if (is_string($optionsValue))
                                        $params['assets'][$assetType]['cache'][$cacheOption] = Tools::castValue($optionsValue);
                                }
                            }
                        }
                    }
                }
            }

            // Add
            TemplateManager::addTemplate($name, TemplateManager::factory($datas['class'], $params));
        }
    }

}

?>
