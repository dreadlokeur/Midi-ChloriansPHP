<?php

namespace framework\mvc\template;

use framework\mvc\ITemplate;
use framework\mvc\Template;

class Phtml implements ITemplate {

    public function __construct($config);

    public function getName();

    public function getPath();

    public function getCharset();

    public function setSslUrlAssets($ssl = true);

    public function getAsset($assetName, $assetType = Template::ASSET_TYPE_PATH);

    public function getAssetsRootUrl($rootDirectory = null);

    public function __get($name);

    public function set($name, $value, $safeValue = false, $forceReplace = false);

    public function merge($vars, $safeValue = false, $forceReplace = false);

    public function unsetVar($name);

    public function unsetVars();

    public function setTemplateFile($file);

    public function getTemplateFile();

    public function displayTemplate();

    public function getTemplateFileContents($file = false);
}

?>
