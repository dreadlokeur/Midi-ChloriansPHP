<?php

namespace framework;

use framework\utility\Validate;
use framework\utility\Date;
use framework\Logger;

abstract class Cache {

    const EXPIRE_SECOND = Date::SECOND;
    const EXPIRE_MINUTE = Date::MINUTE;
    const EXPIRE_HOUR = Date::HOUR;
    const EXPIRE_DAY = Date::DAY;
    const EXPIRE_WEEK = Date::WEEK;
    const EXPIRE_MONTH = Date::MONTH;
    const EXPIRE_YEAR = Date::YEAR;
    const EXPIRE_INFINITE = 0;
    //types
    const TYPE_TIME = 'time';
    const TYPE_NUMBER = 'number';

    protected $_name = '';
    protected $_prefix = '';
    protected $_path = null;
    protected $_gcType = false;
    protected $_gcOption = 500;
    protected $_groups = array();
    protected $_prefixGroups = '';
    protected $_lockName = 'lock';
    protected $_gcName = 'gc';
    protected static $_caches = array();

    public static function getCache($name) {
        if (!is_string($name))
            throw new \Exception('Cache name must be a string');
        if (!array_key_exists($name, self::$_caches))
            return false;

        return self::$_caches[$name];
    }

    public static function getCaches() {
        return self::$_caches;
    }

    public static function addCache($name, $conf, $forceReplace = false) {
        if (!is_string($name) && !is_int($name))
            throw new \Exception('Cache name must be string or integer');


        if (array_key_exists($name, self::$_caches)) {
            if (!$forceReplace)
                throw new \Exception('Cache : "' . $name . '" already defined');

            Logger::getInstance()->debug('Cache : "' . $name . '" already defined, was overloaded');
        }

        self::$_caches[$name] = $conf;
    }

    public function __destruct() {
        // Garbage collection, remove all expired cached datas
        if ($this->_gcType) {
            $gc = $this->read($this->_gcName, null);
            // no exist
            if (is_null($gc))
                $this->_writeGc($gc); // set gc state
            else {
                //check
                if ($this->_needGc($gc)) {
                    $this->clear(); //Delete expired datas
                    $this->delete($this->_gcName);
                } else {
                    if ($this->_gcType == self::TYPE_NUMBER)
                        $this->_writeGc($gc); // increment gc state
                }
            }

            // one call
            $this->_gcType = false;
        }
    }

    public function init($params) {
        if (!is_array($params))
            throw new \Exception('Must be an array');
        if (!isset($params['name']) || !is_string($params['name']))
            throw new \Exception('Miss name parameter, or is invalid');
        $this->_name = $params['name'];

        if (isset($params['prefix'])) {
            if (!is_string($params['prefix']) || !Validate::isVariableName($params['prefix']))
                throw new \Exception('Must be a valid string');
            $this->_prefix = $params['prefix'];
        }

        Logger::getInstance()->addGroup('cache' . $this->_name, 'Cache ' . $this->_name, true);

        //garbage collector setting
        $gcType = isset($params['gc']) ? $params['gc'] : false;
        $gcOption = isset($params['gcOption']) ? $params['gcOption'] : null;
        $this->setGc($gcType, $gcOption);

        if (isset($params['groups'])) {
            //update prefixGroups
            $this->_prefixGroups = str_replace(',', '-', $params['groups'] . '-');

            $groups = explode(',', $params['groups']);
            $this->_groups = $groups;
        }
    }

    public function setGc($gcType, $gcOption) {
        if ($gcType && $gcType != self::TYPE_TIME && $gcType != self::TYPE_NUMBER)
            throw new \Exception('Invalid garbage collector type');
        $this->_gcType = $gcType;

        if ($gcOption && !is_int($gcOption))
            throw new \Exception('Must be null or an integer');
        $this->_gcOption = $gcOption;
    }

    public function increment($key, $offset = 1, $startValue = 1) {
        $this->_crement($key, $offset, true, $startValue);
        Logger::getInstance()->debug('Increment : "' . $key . '"', 'cache' . $this->_name);
    }

    public function decrement($key, $offset = 1, $startValue = 1) {
        $this->_crement($key, $offset, false, $startValue);
        Logger::getInstance()->debug('Decrement : "' . $key . '"', 'cache' . $this->_name);
    }

    protected function _crement($key, $offset = 1, $increment = true, $startValue = 1) {
        if (!$this->isExpired($key) && !$this->isLocked($key)) {
            $val = $this->read($key);
            if (is_null($val)) {
                if (!is_int($startValue) || $startValue >= 0)
                    throw new \Exception('startValue must be an int');
                $this->write($key, $startValue, true);
                return;
            }
            if (!is_int($offset) || $offset == 0)
                throw new \Exception('Offset must be an int');
            if (!is_int($val))
                throw new \Exception('Key value must be an int');

            $increment = $increment ? $val + $offset : $val - $offset;
            $this->write($key, $increment, true, $this->getExpire($key));
        }
    }

    public function getExpire($key) {
        return $this->read($key, null, false, true);
    }

    public static function factory($cacheDriver, $cacheOptions = array()) {
        $class = class_exists('framework\cache\drivers\\' . ucfirst($cacheDriver)) ? 'framework\cache\drivers\\' . ucfirst($cacheDriver) : $cacheClass;
        $inst = new \ReflectionClass($class);
        if (!in_array('framework\\cache\\IDrivers', $inst->getInterfaceNames()))
            throw new \Exception('Cache class must be implement framework\cache\IDrivers');

        return $inst->newInstance($cacheOptions);
    }

    // clear group/groups into caches/cache
    //false == alls, or array for multi, or string for single (caches && groups)
    public static function clearGroupsAllCaches($caches = false, $groups = false) {
        $caches = self::_getCachesList($caches);
        foreach ($caches as $cache) {
            if (!$groups)//alls groups
                $cache->clearGroups();
            else {//groups list
                if (is_array($groups)) {
                    foreach ($groups as &$group)
                        $cache->clearGroup($group);
                } elseif (is_string($groups))
                    $cache->clearGroup($groups);
            }
        }
    }

    protected static function _getCachesList($caches) {
        if (!$caches)
            return self::getCaches();

        $list = array();
        if (!is_array($caches)) {
            foreach ($caches as $cache) {
                $cache = self::getCache($cache);
                if ($cache)
                    $list[] = $cache;
            }
        } elseif (is_string($caches))
            $list[] = $caches;

        return $list;
    }

    public function clearGroups() {
        foreach ($this->_groups as &$group)
            $this->clearGroup($group);

        Logger::getInstance()->debug('Cache cleared groups', 'cache' . $this->_name);
    }

    protected function _isLock($key) {
        return stripos($key, 'lock') !== false;
    }

}

?>