<?php

namespace framework\mvc;

use framework\mvc\Template;
use framework\Config;
use framework\Logger;
use framework\mvc\Dispatcher;
use framework\network\http\Header;
use framework\Session;

abstract class Controller {

    const HTML = 1;
    const XML = 2;
    const JSON = 3;

    protected $_template;
    protected $_templateInitialized = false;
    protected $_autoCallDisplay = true;
    protected $_isAjax = false;
    protected $_ajaxDatas = '';
    protected $_ajaxDatasType = self::JSON;
    protected $_ajaxDatasCache = false;

    public function getDebug() {
        return Dispatcher::getDebug();
    }

    public function isTemplateInitialized() {
        return $this->_templateInitialized;
    }

    public function initTemplate($forceReplace = false) {
        if ($this->_templateInitialized && !$forceReplace)
            return;

        $tpl = Template::getTemplate();
        if (!$tpl)
            return;

        $this->_template = new Template($tpl);
        $this->_templateInitialized = true;
        if (self::getDebug())
            Logger::getInstance()->debug('Root controller class initialize template', 'dispatcher');
    }

    public function __get($name) {
        if ($name == 'tpl') {
            if (!$this->_templateInitialized)
                $this->initTemplate();

            return $this->_template;
        }
        if ($name == 'dispatcher')
            return Dispatcher::getInstance();
        if ($name == 'session')
            return Session::getInstance();
        if ($name == 'config')
            return Config::getInstance();
        if ($name == 'log')
            return Logger::getInstance();
    }

    public function display() {
        if ($this->_isAjax) {
            // No cache
            if (!$this->_ajaxDatasCache) {
                Header::sentHeader('Cache-Control', 'no-cache, must-revalidate');
                Header::sentHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
            }
            switch ($this->_ajaxDatasType) {
                case self::HTML:

                    Header::sentHeader('Content-type', 'text/html');
                    foreach ($this->_ajaxDatas as $data)
                        echo $data;
                    break;
                case self::XML:
                    throw new \Exception('not yet');
                    break;
                case self::JSON:
                    Header::sentHeader('Content-type', 'application/json');
                    echo json_encode((object) $this->_ajaxDatas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    break;
                default:
                    throw new \Exception('Ajax datas type not valid');
                    break;
            }
        } else {
            if (!$this->_templateInitialized)//try init
                $this->initTemplate();

            if ($this->_templateInitialized) {
                $display = $this->_template->displayTemplate();
                if (self::getDebug() && $display)
                    Logger::getInstance()->debug('Display template file : "' . $this->_template->getTemplateFile() . '"', 'dispatcher');
                elseif (self::getDebug() && !$display)
                    Logger::getInstance()->debug('Trying display template file, but no template setted', 'dispatcher');
            }
        }
    }

    public function setAutoCallDisplay($autoCallDisplay) {
        if (!is_bool($autoCallDisplay))
            throw new \Exception('autoCallDisplay parameter must be a boolean');
        $this->_autoCallDisplay = $autoCallDisplay;
    }

    public function getAutoCallDisplay() {
        return $this->_autoCallDisplay;
    }

    public function setAjaxController($ajaxDatasType = self::JSON, $desactivateLoggerDisplayer = true, $ajaxDatasCache = false) {
        if ($ajaxDatasType != self::HTML && $ajaxDatasType != self::XML && $ajaxDatasType != self::JSON)
            throw new \Exception('ajax datas type parameter must be a valid data type : htmt(1), xml(2) or json(3)');
        if (!is_bool($ajaxDatasCache))
            throw new \Exception('ajaxDatasCache parameter must be a boolean');

        $this->_ajaxDatasCache = $ajaxDatasCache;
        $this->_ajaxDatasType = $ajaxDatasType;
        $this->_isAjax = true;
        if ($desactivateLoggerDisplayer) {
            $logger = Logger::getInstance();
            $displayer = $logger->getObservers('display');
            if (!is_null($displayer)) {
                $displayer->setActivated(false);
                $logger->detach($displayer);
            }
        }
    }

    public function isAjaxController($bool) {
        $this->_isAjax = $bool;
    }

    public function addAjaxDatas($key, $datas) {
        $this->_ajaxDatas[$key] = $datas;
        return $this;
    }

}

?>