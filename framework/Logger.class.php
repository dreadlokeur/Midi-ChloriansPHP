<?php

namespace framework;

use framework\utility\Benchmark;
use framework\Application;
use framework\Cache as CacheManager;

class Logger implements \SplSubject {

    use pattern\Singleton,
        cache\Cache;

    const EMERGENCY = 0;
    const ALERT = 1;
    const CRITICAL = 2;
    const ERROR = 3;
    const WARNING = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    protected static $_logLevel = self::WARNING;
    protected static $_logBacktrace = false;
    protected $_observers; // SplObjectStorage
    protected $_logs = array();
    protected $_countLogs = 0;
    protected $_groups = array();

    protected function __construct() {
        $this->_observers = new \SplObjectStorage();
        Benchmark::getInstance('logger')->startTime()->startRam();
    }

    public function __destruct() {
        // call destructor of caches
        $caches = CacheManager::getCaches();
        foreach ($caches as $cache)
            $cache->__destruct();


        if (Application::getDebug() || self::getLevel() == self::DEBUG) {
            if (Application::getProfiler()) {
                // Logger debug informations and benchmark
                $this->addGroup('logger', 'Logger Benchmark and Informations', true);
                $this->debug($this->_observers->count() . ' observers registered', 'logger');
                $this->debug(count($this->getGroups()) . ' groups and ' . ($this->countLogs() + 3) . ' logs', 'logger');
                $this->debug('In aproximately ' . Benchmark::getInstance('logger')->stopTime()->getStatsTime() . ' ms', 'logger');
                $this->debug('Aproximately memory used  : ' . Benchmark::getInstance('logger')->stopRam()->getStatsRam() . ' KB', 'logger');

                // Global informations && Benchmark
                $this->addGroup('global', 'Global Benchmark and Informations', true);
                // Benchmark
                $this->debug('Page generated in aproximately : ' . Benchmark::getInstance('global')->stopTime()->getStatsTime() . ' ms', 'global');
                $this->debug('Aproximately memory used  : ' . Benchmark::getInstance('global')->stopRam()->getStatsRam() . ' KB - Memory allocated : ' . memory_get_peak_usage(true) / 1024 . ' KB', 'global');
            }
        }

        // Notify observers for writting logs
        $this->notify();
    }

    public static function setLevel($level) {
        if (!is_int($level) || $level < self::EMERGENCY || $level > self::DEBUG)
            throw new \Exception('Log level is invalid');
        self::$_logLevel = $level;
    }

    public static function setLogBacktrace($boolean) {
        if (!is_bool($boolean))
            throw new \Exception('Log backtrace parameter must be a boolean');
        self::$_logBacktrace = $boolean;
    }

    public static function getLevel() {
        return self::$_logLevel;
    }

    public static function getLogBacktrace() {
        return self::$_logBacktrace;
    }

    public static function getLevelName($levelNumber) {
        switch ($levelNumber) {
            case self::EMERGENCY:
                $levelName = 'EMERGENCY';
                break;
            case self::ALERT:
                $levelName = 'ALERT';
                break;
            case self::CRITICAL:
                $levelName = 'CRITIC';
                break;
            case self::ERROR:
                $levelName = 'ERROR';
                break;
            case self::WARNING:
                $levelName = 'WARNING';
                break;
            case self::NOTICE:
                $levelName = 'NOTICE';
                break;
            case self::INFO:
                $levelName = 'INFO';
                break;
            case self::DEBUG:
                $levelName = 'DEBUG';
                break;
            default:
                $levelName = 'UNDEFINED LEVEL';
                break;
        }
        return $levelName;
    }

    public function purgeLogs($notify = false) {
        if ($notify)
            $this->notify();

        $this->_logs = array();
    }

    public function purgeGroups($notify = false) {
        if ($notify)
            $this->notify();

        $this->_groups = array();
    }

    public function attach(\SplObserver $observer, $observerName = false, $cloneObserver = false) {
        if ($this->_observers->contains($observer) && !$cloneObserver)
            throw new \Exception('Observer is already attached');

        if ($observerName) {
            $this->_observers->rewind();
            while ($this->_observers->valid()) {
                $this->_observers->key();
                if ($this->_observers->getInfo() == $observerName && !$cloneObserver)
                    throw new \Exception('Observer is already attached');
                $this->_observers->next();
            }
        }

        $this->_observers->attach($observer, $observerName);
        return $this;
    }

    public function detach(\SplObserver $observer) {
        if (!$this->_observers->contains($observer))
            throw new \Exception('Observer don\'t exists');
        $this->_observers->detach($observer);
        return $this;
    }

    public function getObservers($observerName = false) {
        if (!$observerName)
            return $this->_observers;
        else {
            $this->_observers->rewind();
            while ($this->_observers->valid()) {
                $this->_observers->key();

                if ($this->_observers->getInfo() == $observerName)
                    return $this->_observers->current();

                $this->_observers->next();
            }
        }
        return null;
    }

    public function notify($lastLogOnly = false, $reset = true) {
        if ($this->_observers->count() && $this->countLogs() > 0) {
            $logs = $this->getLogs();
            if ($lastLogOnly)
                $logs = array(end($logs));

            foreach ($this->_observers as $observer)
                $observer->update($this, $logs, $this->getGroups());


            if ($lastLogOnly) {
                array_pop($this->_logs);
            } else {
                // avoid multicall
                if ($reset) {
                    $this->_groups = array();
                    $this->_logs = array();
                    $this->_observers = new \SplObjectStorage();
                }
            }
        }
    }

    public function getLogs() {
        return $this->_logs;
    }

    public function getGroups() {
        return $this->_groups;
    }

    public function addGroup($name, $label, $onBottom = false, $forceReplace = false) {
        // Check
        if (!is_string($label))
            throw new \Exception('Label of group must be a string');
        if (array_key_exists((string) $name, $this->getGroups()) && !$forceReplace)
            throw new \Exception('Group : "' . $name . '" aleadry defined');

        $this->_logs[] = array();
        // Set group
        $group = new \stdClass();
        $group->label = $label;
        $group->key = count($this->getLogs()) - 1;
        $group->onBottom = $onBottom;
        // Add group on groups
        $this->_groups[$name] = $group;
    }

    public function deleteGroup($name) {
        if (!is_string($name))
            throw new \Exception('Name of group must be a string');
        $groups = $this->getGroups();
        if (isset($groups[$name])) {
            unset($groups[$name]);
            if (isset($this->_logs[$name]))
                unset($this->_logs[$name]);
        }
    }

    public function fatal($message, $writingImmediately = true) {
        $this->_addLog($message, self::EMERGENCY, false, $writingImmediately);
    }

    public function emergency($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::EMERGENCY, $groupName, $writingImmediately);
    }

    public function alert($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::ALERT, $groupName, $writingImmediately);
    }

    public function critical($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::CRITICAL, $groupName, $writingImmediately);
    }

    public function error($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::ERROR, $groupName, $writingImmediately);
    }

    public function warning($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::WARNING, $groupName, $writingImmediately);
    }

    public function notice($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::NOTICE, $groupName, $writingImmediately);
    }

    public function info($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::INFO, $groupName, $writingImmediately);
    }

    public function debug($message, $groupName = false, $writingImmediately = false) {
        $this->_addLog($message, self::DEBUG, $groupName, $writingImmediately);
    }

    public function countLogs() {
        return $this->_countLogs;
    }

    protected function _addLog($message, $level, $groupName = false, $writingImmediately = false, $isBacktrace = false) {
        if (($level <= $this->getLevel() || $this->getLevel() == self::DEBUG || Application::getDebug())) {
            //cache log
            if (self::getCache()) {
                $hash = md5($message . $level);
                $cache = self::getCache()->read($hash);
                if (!$cache) {
                    self::getCache()->write($hash, $hash, true, $cache::EXPIRE_HOUR);
                    return;
                }
            }

            // set log
            $date = new \DateTime('now');
            $log = new \stdClass();
            $log->message = $message;
            $log->level = $level;
            $log->group = $groupName;
            $log->date = $date->format('Y-m-d H:i:s');
            $log->writingImmediately = $writingImmediately;
            $log->isTrace = $isBacktrace;



            // add log on logs
            if ($groupName)
                $this->_addLogOnGroup($groupName, $log);
            else
                $this->_logs[] = $log;

            if ($writingImmediately)
                $this->notify(true);

            $this->_countLogs++;

            if (!$isBacktrace && self::getLogBacktrace())
                $this->_logBackTrace();
        }
    }

    protected function _addLogOnGroup($groupName, $log) {
        if ($groupName && !array_key_exists((string) $groupName, $this->getGroups()))
            throw new \Exception('The group "' . $groupName . '" don`t exists');
        $this->_logs[$this->_groups[$groupName]->key][] = $log;
    }

    protected function _logBackTrace() {
        $traceArray = array_reverse(debug_backtrace());
        $size = count($traceArray);
        foreach ($traceArray as $level => &$trace) {
            $log = '';
            if (array_key_exists('file', $trace) && array_key_exists('line', $trace))
                $log .= $trace['file'] . ':' . $trace['line'];
            if (array_key_exists('class', $trace) && array_key_exists('function', $trace))
                $log .= (!empty($log) ? ' - ' : '') . $trace['class'] . '::' . $trace['function'];
            $this->_addLog($log, self::DEBUG, false, false, true);
            if ($level > $size - 3)
                break;
        }
    }

}

?>