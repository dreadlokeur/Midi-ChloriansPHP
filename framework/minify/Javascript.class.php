<?php

namespace framework\minify;

class Javascript {

    protected $_compress = false;
    protected $_cached = true;
    protected $_cacheDir = '';
    protected $_cacheName = 'cacheJs.js';
    protected $_cacheNameHash = 'cacheJs.meta';
    protected $_files = array();
    protected $_forceCacheUpdate = false;

    public function __construct($cached) {
        $this->setCached($cached);
    }

    public function setCompressed($compress) {
        if (!is_bool($compress))
            throw new \Exception('Compressed parameter must be a boolean');
        $this->_compress = $compress;
        return $this;
    }

    public function setCached($bool) {
        if (!is_bool($bool))
            throw new \Exception('Cached parameter must be a boolean');
        $this->_cached = (bool) $bool;
        return $this;
    }

    public function setCacheName($cacheName) {
        if (!is_string($cacheName))
            throw new \Exception('cacheName parameter must be a string');
        $this->_cacheName = $cacheName;
        return $this;
    }

    public function getCacheName() {
        return $this->_cacheName;
    }

    public function setCacheHashName($cacheNameHash) {
        if (!is_string($cacheNameHash))
            throw new \Exception('cacheNameHash parameter must be a string');
        $this->_cacheNameHash = $cacheNameHash;
        return $this;
    }

    public function setForceCacheUpdate($bool) {
        if (!is_string($bool))
            throw new \Exception('forceCacheUpdate parameter must be a boolean');
        $this->_forceCacheUpdate = (bool) $bool;
        return $this;
    }

    public function setCacheDir($directory) {
        if (!is_dir($directory))
            throw new \Exception('Directory ' . $directory . ' don\'t exist');
        if (!is_writable($directory))
            throw new \Exception('Directory ' . $directory . ' is not writtable');

        $this->_cacheDir = $directory;
        return $this;
    }

    public function getCacheDir() {
        return $this->_cacheDir;
    }

    public function addFile($file, $alreadyCompressed = false) {
        if ($this->_cacheDir . 'injectJsString.js' == $file)
            return;
        if (!file_exists($file) || !is_file($file))
            throw new \Exception('File ' . $file . ' don\'t exist');
        if (!is_bool($alreadyCompressed))
            throw new \Exception('alreadyCompressed parameter must be a boolean');


        $this->_files[] = array(
            'name' => $file,
            'lastUpdate' => filemtime($file),
            'alreadyCompressed' => (bool) $alreadyCompressed
        );
    }

    public function output() {
        if ($this->_cached) {
            if (!is_dir($this->_cacheDir))
                throw new \Exception('Cache directory not setted');
            //Exipred cache, or force update
            if ($this->_cacheExpired() || $this->_forceCacheUpdate)
                $this->_generateCache();
        }
        else
            echo $this->_getContent();
    }

    protected function _cacheExpired() {
        if (!file_exists($this->_cacheDir . $this->_cacheName) || !file_exists($this->_cacheDir . $this->_cacheNameHash))
            return true;
        // Check Hash liste files
        $hash = file_get_contents($this->_cacheDir . $this->_cacheNameHash);
        if ($hash != md5(serialize($this->_files)))
            return true;
        // Check file updated
        $cacheDate = filemtime($this->_cacheDir . $this->_cacheName);
        foreach ($this->_files as $file) {
            if ($cacheDate < $file['lastUpdate'])
                return true;
        }

        return false;
    }

    protected function _generateCache() {
        file_put_contents($this->_cacheDir . $this->_cacheName, $this->_getContent());
        file_put_contents($this->_cacheDir . $this->_cacheNameHash, md5(serialize($this->_files)));
    }

    protected function _getContent() {
        $notCompressed = $content = '';
        foreach ($this->_files as $file) {
            //On stock le contenu
            $js = file_get_contents($file['name']);
            //On compresse, si l'option est demandé
            if ($this->_compress && !$file['alreadyCompressed']) {
                // Compress file with Javascript Packer plugin
                $packer = new \JavaScriptPacker($js);
                $notCompressed .= trim($packer->pack());
            }
            else
                $content .= $js;

            if (substr($notCompressed, -1) != ';')
                $notCompressed .= ';';
        }

        return $content . $notCompressed;
    }

}

?>