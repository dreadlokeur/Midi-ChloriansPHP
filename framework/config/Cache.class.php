<?php

namespace framework\config;

use framework\Config;
use framework\utility\Tools;
use framework\Logger;
use framework\Cache as CacheFactory;

class Cache extends Config {

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

                foreach ($xml->cache as $cache) {
                    if (!isset($cache->name))
                        throw new \Exception('Miss cache name value');
                    $name = Tools::castValue((string) $cache->name);
                    if (array_key_exists($name, self::$_caches))
                        Logger::getInstance()->debug('Cache : "' . $name . '" already defined, was overloaded');
                    if (!isset($cache->class))
                        throw new \Exception('Miss cache class value');

                    $options = array();
                    foreach ($cache->options->option as $option) {
                        if (count($option->value) > 1) {
                            $values = array();
                            for ($i = 0; $i < count($option->value); $i++)
                                $values[] = Tools::castValue((string) $option->value[$i]);
                            $options[(string) $option->name] = $values;
                        }
                        else
                            $options[(string) $option->name] = Tools::castValue((string) $option->value);
                    }
                    $options['debug'] = isset($cache->debug) ? Tools::castValue((string) $cache->debug) : false;
                    $options['name'] = $name;
                    self::$_caches[$name] = CacheFactory::factory((string) $cache->class, $options);
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
