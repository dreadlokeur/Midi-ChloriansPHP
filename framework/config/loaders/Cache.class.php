<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\Cache as CacheManager;

class Cache extends Loader {

    public function load(Reader $reader) {
        $caches = $reader->read();
        foreach ($caches as $cacheName => $cacheValue) {
            if (empty($cacheName) || !is_string($cacheName))
                throw new \Exception('Name of cache must be as non empty string');
            if (!Validate::isVariableName($cacheName))
                throw new \Exception('Name of cache must be a valid variable name');

            $values = (object) $cacheValue;
            $params = array();
            foreach ($values as $name => $value) {
                if ($name == 'comment')
                    continue;

                if (is_string($value))
                    $value = Tools::castValue($value);
                $params[(string) $name] = $value;
            }

            if (!isset($params['driver']))
                throw new \Exception('Miss driver parameter for cache : "' . $cacheName . '"');
            $params['name'] = $cacheName;
            CacheManager::addCache($cacheName, CacheManager::factory($params['driver'], $params), true);
        }
    }

}

?>
