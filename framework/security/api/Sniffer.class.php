<?php

namespace framework\security\api;

use framework\security\IApi;
use framework\security\Api;
use framework\Logger;
use framework\utility\Validate;
use framework\utility\Tools;
use framework\network\Http;
use framework\Session;
use framework\mvc\Dispatcher;

class Sniffer extends Api implements IApi {

    const CRAWLER_BAD = 'bad';
    const CRAWLER_GOOD = 'good';
    const CRAWLER_UNKNOWN = 'unknown';

    protected static $_isRun = false;
    protected $_trapName = 'trap';
    protected $_badCrawlerFile = null;
    protected $_goodCrawlerFile = null;
    protected $_logBadCrawler = false;
    protected $_logGoodCrawler = false;
    protected $_logUnknownCrawler = false;

    public function __construct($options = array()) {
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
    }

    public function run() {
        if (self::$_isRun)
            throw new \Exception('Sniffer API already run');

        // set bot into session
        if (Http::getQuery($this->_trapName) && !Validate::isGoogleBot())
            Session::getInstance()->add('crawler', true, true, true);

        $this->_check();

        if (self::getDebug())
            Logger::getInstance()->debug('Sniffer API was run', 'securityApi');
        self::$_isRun = true;
    }

    protected function _check() {
        if (Session::getInstance()->get('crawler')) {
            $isBadCrawler = false;
            $isGoodCrawler = false;
            $ip = Tools::getUserIp();
            $userAgent = Http::getServer('HTTP_USER_AGENT');

            if ($this->_badCrawlerFile)
                $badCrawlerXml = simplexml_load_file($this->_badCrawlerFile);
            if ($this->_goodCrawlerFile)
                $goodCrawlerXml = simplexml_load_file($this->_goodCrawlerFile);

            if ($badCrawlerXml) {
                $badCrawlerList = $badCrawlerXml->crawler;
                foreach ($badCrawlerList as $crawler) {
                    if (isset($crawler->ip) && (string) $crawler->ip == $ip)
                        $isBadCrawler = true;
                    if (isset($crawler->userAgent) && strripos((string) $crawler->userAgent, $userAgent) !== false)
                        $isBadCrawler = true;
                    if ($isBadCrawler) {
                        $this->_catch($ip, $userAgent, self::CRAWLER_BAD);
                        // Redirection vers 403
                        Dispatcher::getInstance()->show403(true);
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

    public function stop() {
        self::$_isRun = false;
        if (self::getDebug())
            Logger::getInstance()->debug('Sniffer API was stopped', 'securityApi');
    }

}

?>