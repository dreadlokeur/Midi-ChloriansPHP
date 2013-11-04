<?php

namespace framework\mvc;

interface ITemplate {

    public function __construct($params);

    public function setName($name);

    public function getName();

    public function setPath($path);

    public function getPath();

    public function setCharset($charset);

    public function getCharset();

    public function setAssets($assets);

    public function getAssets();

    public function initAssets();

    public function __get($name);

    public function getVar($name, $default = null); //alias

    public function setVar($name, $value, $safeValue = false, $forceReplace = false);

    public function mergeVar($vars, $safeValue = false, $forceReplace = false);

    public function deleteVar($name);

    public function purgeVars();

    public function setFile($file);

    public function getFile();

    public function getFileContents($file = false, $parse = false);

    public function parse();

    public function display();

    public function getUrl($routeName, $vars = array(), $lang = null, $ssl = false);

    public function getUrlAsset($type, $ssl = false);
}

?>