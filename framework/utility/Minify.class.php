<?php

namespace framework\utility;

use framework\Cache;
use framework\utility\Tools;
use framework\utility\Validate;

class Minify {

    const TYPE_CSS = 'css';
    const TYPE_JS = 'js';

    protected $_cache = null;
    protected $_compress = false;
    protected $_path = null;
    protected $_type = null;
    protected $_files = array();
    protected $_key = '';
    protected $_content = '';

    public function __construct($cacheName, $path, $type, $compress = true) {
        $this->setCache($cacheName);
        $this->setPath($path);
        $this->setType($type);
        if ($compress)
            $this->setCompress($compress);
    }

    public function setCache($cacheName) {
        $cache = Cache::getCache($cacheName);
        if (!$cache)
            throw new \Exception('Cache : "' . $cacheName . '" is not a valid cache');
        $this->_cache = $cache;
        return $this;
    }

    public function getCache() {
        return $this->_cache;
    }

    public function setType($type) {
        if ($type != self::TYPE_CSS && $type != self::TYPE_JS)
            throw new \Exception('Invalid minifier type');
        $this->_type = $type;
    }

    public function getType() {
        return $this->_type;
    }

    public function setCompress($compress) {
        if (!is_bool($compress))
            throw new \Exception('Compress parameter must be a boolean');
        $this->_compress = $compress;
    }

    public function getCompress() {
        return $this->_compress;
    }

    public function setPath($path) {
        if (!is_dir($path))
            throw new \Exception('Path ' . $path . ' don\'t exist');
        if (!is_readable($path))
            throw new \Exception('Path ' . $path . ' is not readable');
        $this->_path = $path;
        return $this;
    }

    public function getPath() {
        return $this->_path;
    }

    public function addFile($file, $alreadyCompressed = false) {
        if (!file_exists($file) || !is_file($file))
            throw new \Exception('File ' . $file . ' don\'t exist');

        $this->_files[] = array(
            'name' => $file,
            'filemtime' => filemtime($file),
            'alreadyCompressed' => (bool) $alreadyCompressed
        );
    }

    public function minify($returnContent = true, $forceCacheUpdate = false) {
        // autoloading files
        foreach (Tools::cleanScandir($this->getPath()) as $file) {
            if (Validate::isFileExtension('css', $file))
                $this->addFile($this->getPath() . $file);
        }

        $this->_key = md5($this->getPath());
        if ($this->_cacheExpired() || $forceCacheUpdate)
            $this->_generateCache();

        if ($returnContent)
            return $this->getContent();
    }

    public function getContent() {
        return $this->_content;
    }

    protected function _cacheExpired() {
        $content = $this->_cache->read($this->_key . 'content');
        if (is_null($content))
            return true;
        $filesList = $this->_cache->read($this->_key . 'filesList');
        if (is_null($filesList) || md5(serialize($this->_files)) != $filesList)
            return true;

        $filemtime = $this->_cache->read($this->_key . 'filemtime');
        if (is_null($filemtime))
            return true;
        foreach ($this->_files as $file) {
            if ($filemtime < $file['filemtime'])
                return true;
        }

        $this->_content = $content;
        return false;
    }

    protected function _generateCache() {
        $content = $this->_getContent();
        $this->_cache->write($this->_key . 'content', $content, true);
        $this->_cache->write($this->_key . 'filesList', md5(serialize($this->_files)), true);
        $this->_cache->write($this->_key . 'filemtime', time(), true);

        $this->_content = $content;
    }

    protected function _getContent() {
        //TODO rework better clean...
        if ($this->_type == self::TYPE_CSS) {
            $content = '';
            foreach ($this->_files as $file) {
                $f = file_get_contents($file['name']);
                if ($this->_compress && !$file['alreadyCompressed']) {
                    $content .= $this->_compressCss($f);
                    continue;
                }
                $content .= $f;
            }
            return $content;
        } elseif ($this->_type == self::TYPE_JS) {
            $notCompressed = $content = '';
            foreach ($this->_files as $file) {
                $js = file_get_contents($file['name']);
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

    protected function _compressCss($buffer) {
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
