<?php

namespace framework\security\adaptaters;

use framework\Logger;
use framework\Session;
use framework\mvc\Router;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\network\Http;
use framework\security\IAdaptater;

class Sniffer implements IAdaptater {

    const CRAWLER_BAD = 'bad';
    const CRAWLER_GOOD = 'good';
    const CRAWLER_UNKNOWN = 'unknown';

    protected $_name = null;
    protected $_autorun = false;
    protected $_trapName = 'trap';
    protected $_badCrawlerFile = null;
    protected $_goodCrawlerFile = null;
    protected $_logBadCrawler = false;
    protected $_logGoodCrawler = false;
    protected $_logUnknownCrawler = false;

    public function __construct($options = array()) {
        if (!isset($options['name']))
            throw new \Exception('Miss param name');
        $this->setName($options['name']);

        if (isset($options['autorun']))
            $this->setAutorun($options['autorun']);

        if (isset($options['trapName']) && Validate::isVariableName($options['trapName']))
            $this->_trapName = $options['trapName'];
        if (isset($options['badCrawlerFile'])) {
            if (!file_exists($options['badCrawlerFile']))
                throw new \Exception('badCrawlerFile does\'t exists');
            if (!Validate::isFileMimeType('xml', $options['badCrawlerFile']))
                throw new \Exception('goodCrawlerFile invalid format, must be xml');
            $this->_badCrawlerFile = $options['badCrawlerFile'];
        }
        if (isset($options['goodCrawlerFile'])) {
            if (!file_exists($options['goodCrawlerFile']))
                throw new \Exception('goodCrawlerFile does\'t exists');
            if (!Validate::isFileMimeType('xml', $options['goodCrawlerFile']))
                throw new \Exception('goodCrawlerFile invalid format, must be xml');
            $this->_goodCrawlerFile = $options['goodCrawlerFile'];
        }

        if (isset($options['logBadCrawler'])) {
            if (!is_bool($options['logBadCrawler']))
                throw new \Exception('logBadCrawler parameter must be a boolean');
            $this->_logBadCrawler = $options['logBadCrawler'];
        }
        if (isset($options['logGoodCrawler'])) {
            if (!is_bool($options['logGoodCrawler']))
                throw new \Exception('logGoodCrawler parameter must be a boolean');
            $this->_logBadCrawler = $options['logGoodCrawler'];
        }
        if (isset($options['logUnknownCrawler'])) {
            if (!is_bool($options['logUnknownCrawler']))
                throw new \Exception('logUnknownCrawler parameter must be a boolean');
            $this->_logUnknownCrawler = $options['logUnknownCrawler'];
        }

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
        $ip = Tools::getUserIp();
        $userAgent = Http::getServer('HTTP_USER_AGENT');
        //badcrawler detected
        if (Session::getInstance()->get(md5($ip . 'badcrawler')))
            Router::getInstance()->show403(true);

        $this->_check($ip, $userAgent);
        Logger::getInstance()->debug('Security was run', 'security' . $this->getName());
    }

    public function stop() {
        $this->flush();
        Logger::getInstance()->debug('Security was stopped', 'security' . $this->getName());
    }

    public function create() {
        
    }

    public function set() {
        
    }

    public function get() {
        
    }

    public function check($checkingValue, $flush = false) {
        
    }

    public function flush() {
        
    }

    protected function _check($ip, $userAgent) {
        if (Http::getQuery($this->_trapName) && !Validate::isGoogleBot()) {
            $isBadCrawler = false;
            $isGoodCrawler = false;

            if ($this->_badCrawlerFile) {
                $badCrawlerXml = simplexml_load_file($this->_badCrawlerFile);
                if (is_null($badCrawlerXml) || !$badCrawlerXml)
                    throw new \Exception('Invalid xml file : "' . $this->_badCrawlerFile . '"');
            }
            if ($this->_goodCrawlerFile) {
                $goodCrawlerXml = simplexml_load_file($this->_goodCrawlerFile);
                if (is_null($goodCrawlerXml) || !$goodCrawlerXml)
                    throw new \Exception('Invalid xml file : "' . $this->_goodCrawlerFile . '"');
            }

            if ($badCrawlerXml) {
                $badCrawlerList = $badCrawlerXml->crawler;
                foreach ($badCrawlerList as $crawler) {
                    if (isset($crawler->ip) && (string) $crawler->ip == $ip)
                        $isBadCrawler = true;
                    if (isset($crawler->userAgent) && strripos((string) $crawler->userAgent, $userAgent) !== false)
                        $isBadCrawler = true;
                    if ($isBadCrawler) {
                        $this->_catch($ip, $userAgent, self::CRAWLER_BAD);
                        Session::getInstance()->add(md5($ip . 'badcrawler'), true, true, true);
                        Router::getInstance()->show403(true);
                        break;
                    }
                }
                unset($crawler);
            }
            if ($goodCrawlerXml) {
                $goodCrawlerList = $goodCrawlerXml->crawler;
                foreach ($goodCrawlerList as $crawler) {
                    if (isset($crawler->ip) && (string) $crawler->ip == $ip)
                        $isGoodCrawler = true;
                    if (isset($crawler->userAgent) && strripos((string) $crawler->userAgent, $userAgent) !== false)
                        $isGoodCrawler = true;
                    if ($isGoodCrawler) {
                        $this->_catch($ip, $userAgent, self::CRAWLER_BAD);
                        break;
                    }
                }
                unset($crawler);
            }
            // unknown
            if (!$isBadCrawler && !$isGoodCrawler)
                $this->_catch($ip, $userAgent, self::CRAWLER_BAD);
        }
    }

    protected function _catch($ip, $userAgent, $type) {
        $log = false;
        if ($this->_logBadCrawler && $type == self::CRAWLER_BAD)
            $log = true;
        if ($this->_goodCrawlerFile && $type == self::CRAWLER_GOOD)
            $log = true;
        if ($this->_logUnknownCrawler && $type == self::CRAWLER_UNKNOWN)
            $log = true;

        if ($log)
            Logger::getInstance()->warning($type . ' crawler detected, ip : "' . $ip . '" and user-agent : "' . $userAgent . '"');
    }

}

?>