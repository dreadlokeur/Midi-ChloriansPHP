<?php

namespace framework\config;

use framework\Config;
use framework\utility\Tools;
use framework\mvc\Template as Tpl;

class Template extends Config {

    protected $_filename = null;
    protected $_format = null;

    public function __construct($filename, $format) {
        if (!file_exists($filename))
            throw new \Exception('File "' . $filename . '" don\'t exists');

        // Check format
        if ($format !== Config::XML && $format !== Config::INI)
            throw new \Exception('Invalid config format');

        $this->_filename = $filename;
        $this->_format = $format;
    }

    public function load() {
        switch ($this->_format) {
            case self::XML:
                $xml = @simplexml_load_file($this->_filename);
                if ($xml === null || $xml === false)
                    throw new \Exception('Invalid xml file');
                $templates = $xml->template;
                foreach ($templates as $template) {
                    $templateValue = new \stdClass();
                    $templateValue->name = Tools::castValue((string) $template->name);

                    $templateValue->path = Tools::castValue((string) $template->path);
                    if (!is_dir($templateValue->path))
                        throw new \Exception('Invalid template path');

                    // Ressource of template
                    $templateValue->assets = new \stdClass();
                    $templateValue->assets->rootDirectory = $templateValue->path . $template->assets->rootDirectory . DS;
                    if (!is_dir($templateValue->assets->rootDirectory))
                        throw new \Exception('Invalid assets path');

                    $assets = $template->assets->asset;
                    foreach ($assets as $asset) {
                        $name = (string) $asset->name;
                        $path = $templateValue->assets->rootDirectory . Tools::castValue((string) $asset->directory) . DS;
                        if (!is_dir($path))
                            throw new \Exception('Invalid asset path : "' . $path . '"');

                        // asset cache
                        if (isset($asset->cache)) {
                            if (isset($asset->cache->fileName))
                                $cache['fileName'] = Tools::castValue((string) $asset->cache->fileName);
                            if (isset($asset->cache->fileMeta))
                                $cache['fileMeta'] = Tools::castValue((string) $asset->cache->fileMeta);
                            if (isset($asset->cache->compress))
                                $cache['compress'] = Tools::castValue((string) $asset->cache->compress);
                            if (!isset($asset->cache->directory))
                                throw new \Exception('Asset "' . $name . '" cache directory miss');

                            $assetCachePath = $path . Tools::castValue((string) $asset->cache->directory) . DS;
                            if (!is_dir($assetCachePath))
                                throw new \Exception('Invalid asset cache path : "' . $assetCachePath . '"');

                            $cache['path'] = $assetCachePath;
                            $cache['url'] = (string) $asset->directory . '/' . Tools::castValue((string) $asset->cache->directory) . '/';
                        }
                        else
                            $cache = null;

                        $loadUrls = isset($asset->loadUrls) ? Tools::castValue((string) $asset->loadUrls) : false;
                        $loadLangs = isset($asset->loadLangs) ? Tools::castValue((string) $asset->loadLangs) : false;

                        $templateValue->assets->$name = array(
                            'url' => (string) $asset->directory . '/',
                            'path' => $templateValue->assets->rootDirectory . Tools::castValue((string) $asset->directory) . DS,
                            'cache' => $cache,
                            'urls' => $loadUrls,
                            'langs' => $loadLangs);
                    }
                    $templateValue->charset = isset($template->charset) ? Tools::castValue((string) $template->charset) : 'UTF-8';

                    // Finally, add loaded conf
                    Tpl::addTemplate($templateValue->name, $templateValue);
                }
                break;
            case self::INI:
                throw new \Exception('NOT YET');
                break;
            default:
                throw new \Exception('Config format must be setted');
                break;
        }
    }

}

?>