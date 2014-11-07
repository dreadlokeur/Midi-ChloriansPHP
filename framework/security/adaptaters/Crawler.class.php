<?php

namespace framework\security\adaptaters;

use framework\Logger;
use framework\Session;
use framework\mvc\Router;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\network\Http;
use framework\security\IAdaptater;

class Crawler implements IAdaptater {

    const CRAWLER_BAD = 'bad';
    const CRAWLER_GOOD = 'good';
    const CRAWLER_UNKNOWN = 'unknown';

    protected $_name = null;
    protected $_autorun = false;
    protected $_queryName = 'crawlertrap';
    protected $_sessionKeyName = 'crawler';
    protected $_badCrawlerBan = true;
    protected $_goodCrawlerBan = false;
    protected $_unknownCrawlerBan = true;
    protected $_badCrawlerLog = true;
    protected $_goodCrawlerLog = true;
    protected $_unknownCrawlerLog = true;
    protected $_badCrawlers = array();
    protected $_goodCrawlers = array();
    protected $_ip = null;
    protected $_userAgent = null;
    protected $_isCrawler = false;

    public function __construct($options = array()) {
        if (!isset($options['name']))
            throw new \Exception('Miss param name');
        $this->setName($options['name']);

        if (isset($options['autorun']))
            $this->setAutorun($options['autorun']);

        if (isset($options['queryName']))
            $this->setQueryName($options['queryName']);

        if (isset($options['sessionKeyName']))
            $this->setSessionKeyName($options['sessionKeyName']);


        if (isset($options['badCrawlerBan']))
            $this->setBadCrawlerBan($options['badCrawlerBan']);
        if (isset($options['goodCrawlerBan']))
            $this->setGoodCrawlerBan($options['goodCrawlerBan']);
        if (isset($options['unknownCrawlerBan']))
            $this->setUnknownCrawlerBan($options['unknownCrawlerBan']);

        if (isset($options['badCrawlerLog']))
            $this->setBadCrawlerLog($options['badCrawlerLog']);
        if (isset($options['goodCrawlerLog']))
            $this->setGoodCrawlerLog($options['goodCrawlerLog']);
        if (isset($options['unknownCrawlerLog']))
            $this->setUnknownCrawlerLog($options['unknownCrawlerLog']);


        if (isset($options['badCrawlers']))
            $this->setBadCrawlers($options['badCrawlers']);
        if (isset($options['goodCrawlers']))
            $this->setGoodCrawlers($options['goodCrawlers']);

        Logger::getInstance()->addGroup('security' . $this->getName(), 'Security ' . $this->getName(), true, true);
    }

    public function setName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('name must be a valid variable name');

        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function setAutorun($autorun) {
        if (!is_bool($autorun))
            throw new \Exception('Autorun must be a boolean');

        $this->_autorun = $autorun;
    }

    public function getAutorun() {
        return $this->_autorun;
    }

    public function run() {
        $this->create();
        $this->_isCrawler = $this->get();
        switch ($this->_isCrawler) {
            case self::CRAWLER_BAD:
                if ($this->getBadCrawlerBan())
                    Router::getInstance()->show403(true);
                break;
            case self::CRAWLER_GOOD:
                if ($this->getGoodCrawlerBan())
                    Router::getInstance()->show403(true);
                break;
            case self::CRAWLER_UNKNOWN:
                if ($this->getUnknownCrawlerBan())
                    Router::getInstance()->show403(true);
                break;
            default:
                break;
        }

        if (!$this->check(Http::getQuery($this->getQueryName())))
            $this->set();

        Logger::getInstance()->debug('Security was run', 'security' . $this->getName());
    }

    public function stop() {
        $this->flush();
        Logger::getInstance()->debug('Security was stopped', 'security' . $this->getName());
    }

    public function create() {
        $this->_ip = Tools::getUserIp();
        $this->_userAgent = Http::getServer('HTTP_USER_AGENT');
        Logger::getInstance()->debug('ip : "' . $this->_ip . '" and user-agent : "' . $this->_userAgent . '"', 'security' . $this->getName());
    }

    public function set() {
        switch ($this->_isCrawler) {
            case self::CRAWLER_BAD:
                Session::getInstance()->add(md5($this->_ip . $this->getSessionKeyName()), self::CRAWLER_BAD, true, true);
                if ($this->getBadCrawlerLog())
                    Logger::getInstance()->warning(self::CRAWLER_BAD . ' crawler detected, ip : "' . $this->_ip . '" and user-agent : "' . $this->_userAgent . '"');

                if ($this->getBadCrawlerBan()) {
                    Router::getInstance()->show403(true);
                }
                break;
            case self::CRAWLER_GOOD:
                Session::getInstance()->add(md5($this->_ip . $this->getSessionKeyName()), self::CRAWLER_GOOD, true, true);
                if ($this->getBadCrawlerLog())
                    Logger::getInstance()->warning(self::CRAWLER_GOOD . ' crawler detected, ip : "' . $this->_ip . '" and user-agent : "' . $this->_userAgent . '"');

                if ($this->getGoodCrawlerBan()) {
                    Router::getInstance()->show403(true);
                }
                break;
            case self::CRAWLER_UNKNOWN:
                Session::getInstance()->add(md5($this->_ip . $this->getSessionKeyName()), self::CRAWLER_UNKNOWN, true, true);
                if ($this->getBadCrawlerLog())
                    Logger::getInstance()->warning(self::CRAWLER_UNKNOWN . ' crawler detected, ip : "' . $this->_ip . '" and user-agent : "' . $this->_userAgent . '"');

                if ($this->getUnknownCrawlerBan()) {
                    Router::getInstance()->show403(true);
                }
                break;
            default:
                break;
        }
    }

    public function get() {
        Logger::getInstance()->debug('Get session key for : "' . $this->_ip . '"', 'security' . $this->getName());
        return Session::getInstance()->get(md5($this->_ip . $this->getSessionKeyName()));
    }

    public function check($checkingValue, $flush = false) {
        if ($flush)
            $this->flush();

        if (!is_null($checkingValue)) {
            foreach ($this->getBadCrawlers() as $badCrawler) {
                if (isset($badCrawler->ip) && (string) $badCrawler->ip == $this->_ip)
                    $this->_isCrawler = self::CRAWLER_BAD;
                if (isset($badCrawler->userAgent) && strripos((string) $badCrawler->userAgent, $this->_userAgent) !== false)
                    $this->_isCrawler = self::CRAWLER_BAD;
            }
            foreach ($this->_goodCrawlers as $goodCrawler) {
                if (isset($goodCrawler->ip) && (string) $goodCrawler->ip == $this->_ip)
                    $this->_isCrawler = self::CRAWLER_GOOD;
                if (isset($goodCrawler->userAgent) && strripos((string) $goodCrawler->userAgent, $this->_userAgent) !== false)
                    $this->_isCrawler = self::CRAWLER_GOOD;
            }

            if (!$this->_isCrawler)
                $this->_isCrawler = self::CRAWLER_UNKNOWN;

            return false;
        }
        Logger::getInstance()->debug('"' . $this->_ip . '" not crawler', 'security' . $this->getName());
        return true;
    }

    public function flush() {
        Session::getInstance()->delete(md5($this->_ip . $this->getSessionKeyName()), true);
    }

    public function setQueryName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('query name must be a valid variable name');

        $this->_queryName = $name;
    }

    public function getQueryName() {
        return $this->_queryName;
    }

    public function setSessionKeyName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Key name must be a valid variable');

        $this->_sessionKeyName = $name;
    }

    public function getSessionKeyName() {
        return $this->_sessionKeyName;
    }

    public function setBadCrawlerBan($bool) {
        if (!is_bool($bool))
            throw new \Exception('badCrawlerBan must be a boolean');

        $this->_badCrawlerBan = $bool;
    }

    public function getBadCrawlerBan() {
        return $this->_badCrawlerBan;
    }

    public function setGoodCrawlerBan($bool) {
        if (!is_bool($bool))
            throw new \Exception('goodCrawlerBan must be a boolean');

        $this->_goodCrawlerBan = $bool;
    }

    public function getGoodCrawlerBan() {
        return $this->_goodCrawlerBan;
    }

    public function setUnknownCrawlerBan($bool) {
        if (!is_bool($bool))
            throw new \Exception('unknownCrawlerBan must be a boolean');

        $this->_unknownCrawlerBan = $bool;
    }

    public function getUnknownCrawlerBan() {
        return $this->_unknownCrawlerBan;
    }

    public function setBadCrawlerLog($bool) {
        if (!is_bool($bool))
            throw new \Exception('badCrawlerLog must be a boolean');

        $this->_badCrawlerLog = $bool;
    }

    public function getBadCrawlerLog() {
        return $this->_badCrawlerLog;
    }

    public function setGoodCrawlerLog($bool) {
        if (!is_bool($bool))
            throw new \Exception('goodCrawlerLog must be a boolean');

        $this->_goodCrawlerLog = $bool;
    }

    public function getGoodCrawlerLog() {
        return $this->_goodCrawlerLog;
    }

    public function setUnknownCrawlerLog($bool) {
        if (!is_bool($bool))
            throw new \Exception('unknownCrawlerLog must be a boolean');

        $this->_unknownCrawlerLog = $bool;
    }

    public function getUnknownCrawlerLog() {
        return $this->_unknownCrawlerLog;
    }

    public function setBadCrawlers($file) {
        if (!file_exists($file))
            throw new \Exception('badCrawlers file does\'t exists');
        if (!Validate::isFileMimeType('xml', $file))
            throw new \Exception('badCrawlers file invalid format, must be xml');

        $badCrawlerDatas = simplexml_load_file($file);
        if (is_null($badCrawlerDatas) || !$badCrawlerDatas || !isset($badCrawlerDatas->crawler))
            throw new \Exception('Invalid xml file : "' . $file . '"');

        $this->_badCrawlers = $badCrawlerDatas->crawler;
    }

    public function getBadCrawlers() {
        return $this->_badCrawlers;
    }

    public function setGoodCrawlers($file) {
        if (!file_exists($file))
            throw new \Exception('goodCrawlers file does\'t exists');
        if (!Validate::isFileMimeType('xml', $file))
            throw new \Exception('goodCrawlers file invalid format, must be xml');

        $goodCrawlerDatas = simplexml_load_file($file);
        if (is_null($goodCrawlerDatas) || !$goodCrawlerDatas || !isset($goodCrawlerDatas->crawler))
            throw new \Exception('Invalid xml file : "' . $file . '"');

        $this->_goodCrawlers = $goodCrawlerDatas->crawler;
    }

    public function getGoodCrawlers() {
        return $this->_goodCrawlers;
    }

}

?>