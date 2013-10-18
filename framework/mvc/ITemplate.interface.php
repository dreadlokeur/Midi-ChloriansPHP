<?php

namespace framework\mvc;

interface ITemplate {

    public function __construct($config);

    public function setName($name);

    public function getName();

    public function setPath($path);

    public function getPath();

    public function setCharset($charset);

    public function getCharset();

    public function __get($name);

    public function getVar($name); //alias

    public function setVar($name, $value, $safeValue = false, $forceReplace = false);

    public function mergeVar($vars, $safeValue = false, $forceReplace = false);

    public function deleteVar($name);

    public function purgeVars();

    public function setFile($file);

    public function getFile();

    public function getFileContents($file = false, $parse = false);

    public function parse();

    public function display();

    public function setSslUrlAssets($ssl = true);

    public function getAsset($assetName, $assetType = self::ASSET_TYPE_PATH);

    public function getAssetsRootUrl($rootDirectory = null);
}

?>
