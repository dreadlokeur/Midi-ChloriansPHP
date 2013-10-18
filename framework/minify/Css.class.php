<?php

// TODO set and get ...
// TODO compress options for all files and file by file
// TODO factoryse minifier for css and js ...
// TODO rewrite url into css ..
// TODO autoloading css files into cssDir ...

namespace framework\minify;

class Css {

    protected $_cached = true;
    protected $_compress = false;
    protected $_cssDir = '';
    protected $_cacheDir = false;
    protected $_cacheName = 'cacheCss.css';
    protected $_cacheNameHash = 'cacheCss.meta';
    protected $_files = array();
    protected $_forceCacheUpdate = false;

    public function __construct($cssDir) {
        $this->setCssDir($cssDir);
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

    public function setCacheHashName($cacheHashName) {
        if (!is_string($cacheHashName))
            throw new \Exception('cacheNameHash parameter must be a string');
        $this->_cacheNameHash = $cacheHashName;
        return $this;
    }

    public function setForceCacheUpdate($bool) {
        if (!is_bool($bool))
            throw new \Exception('ForceCacheUpdate parameter must be a boolean');
        $this->_forceCacheUpdate = (bool) $bool;
        return $this;
    }

    public function setCssDir($directory) {
        if (!is_dir($directory))
            throw new \Exception('Directory ' . $directory . ' don\'t exist');
        if (!is_writable($directory))
            throw new \Exception('Directory ' . $directory . ' is not writtable');
        $this->_cssDir = $directory;
        return $this;
    }

    public function getCssDir() {
        return $this->_cssDir;
    }

    public function setCacheDir($directory) {
        if (!is_dir($directory))
            throw new \Exception('Directory ' . $directory . ' don\'t exist');
        if (!is_writable($directory))
            throw new \Exception('Directory ' . $directory . ' is not writtable');
        $this->_cacheDir = $directory;
        return $this;
    }

    public function getCacheDir($ifNotSettedReturnCssDir = true) {
        if (!$this->_cacheDir && $ifNotSettedReturnCssDir)
            return $this->_cssDir;

        return $this->_cacheDir;
    }

    public function setCompressed($compress) {
        if (!is_bool($compress))
            throw new \Exception('Compressed parameter must be a boolean');
        $this->_compress = $compress;
    }

    public function getCompressed() {
        return $this->_compress;
    }

    public function addFile($file) {
        if ($this->getCacheDir() . $this->_cacheName == $file || $this->getCacheDir() . $this->_cacheNameHash == $file)
            return;
        if (!file_exists($file) || !is_file($file))
            throw new \Exception('File ' . $file . ' don\'t exist');
        $this->_files[] = array(
            'name' => $file,
            'lastUpdate' => filemtime($file),
        );
    }

    public function output() {
        if ($this->_cached) {
            if (!is_dir($this->getCacheDir()))
                throw new \Exception('Css cache directory not setted');

            if ($this->_cacheExpired() || $this->_forceCacheUpdate)
                $this->_generateCache();
        }
    }

    protected function _cacheExpired() {
        if (!file_exists($this->getCacheDir() . $this->_cacheName) || !file_exists($this->getCacheDir() . $this->_cacheNameHash))
            return true;
        // Check Hash liste files
        $hash = file_get_contents($this->getCacheDir() . $this->_cacheNameHash);
        if ($hash != md5(serialize($this->_files)))
            return true;
        // Check file updated
        $cacheDate = filemtime($this->getCacheDir() . $this->_cacheName);
        foreach ($this->_files as $file) {
            if ($cacheDate < $file['lastUpdate'])
                return true;
        }
    }

    protected function _generateCache() {
        file_put_contents($this->getCacheDir() . $this->_cacheName, $this->_getContent());
        file_put_contents($this->getCacheDir() . $this->_cacheNameHash, md5(serialize($this->_files)));
    }

    protected function _getContent() {
        $content = '';
        //On parcours les fichiers css ajoutés
        foreach ($this->_files as $file) {
            $content .= file_get_contents($file['name']);
        }
        if ($this->getCompressed())
            $content = $this->_compress($content);
        return $content;
    }

    protected function _compress($buffer) {
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer); // remove comments
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  '), '', $buffer); // remove tabs, spaces, newlines, etc.
        $buffer = str_replace('{ ', '{', $buffer); // remove unnecessary spaces.
        $buffer = str_replace(' }', '}', $buffer);
        $buffer = str_replace('; ', ';', $buffer);
        $buffer = str_replace(', ', ',', $buffer);
        $buffer = str_replace(' {', '{', $buffer);
        $buffer = str_replace('} ', '}', $buffer);
        $buffer = str_replace(': ', ':', $buffer);
        $buffer = str_replace(' ,', ',', $buffer);
        $buffer = str_replace(' ;', ';', $buffer);
        return $buffer;
    }

}

?>