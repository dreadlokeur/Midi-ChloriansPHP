<?php

namespace framework;

use framework\database\Server;
use framework\Logger;
use framework\Application;

class Database {

    protected static $_databases = array();
    protected $_name = '';
    protected $_type = null;
    protected $_engine = null;
    protected $_engineName = '';
    protected $_masters = array();
    protected $_slaves = array();
    protected $_stats = array('time' => 0, 'ram' => 0); //Queries totals stats
    protected $_queryCount = 0;

    public static function getDatabase($name, $returnEngine = false) {
        if (!is_string($name))
            throw new \Exception('Database name must be a string');

        if (!array_key_exists($name, self::$_databases))
            return false;

        $db = self::$_databases[$name];
        if ($returnEngine)
            return $db->getEngine();

        return $db;
    }

    public static function getDatabases() {
        return self::$_databases;
    }

    public static function addDatabase($name, Database $instance, $forceReplace = false) {
        if (!is_string($name) && !is_int($name))
            throw new \Exception('Database name must be string or integer');


        if (array_key_exists($name, self::$_databases)) {
            if (!$forceReplace)
                throw new \Exception('Database : "' . $name . '" already defined');

            Logger::getInstance()->debug('Database : "' . $name . '" already defined, was overloaded');
        }

        self::$_databases[$name] = $instance;
    }

    public function getStats() {
        return $this->_stats;
    }

    public function setStats($time, $ram) {
        $this->_stats['time'] = $this->_stats['time'] + $time;
        $this->_stats['ram'] = $this->_stats['ram'] + $ram;
    }

    public function __construct($name, $engine) {
        $this->setName($name);
        $this->setEngine($engine);
        Logger::getInstance()->addGroup($this->_name, 'Database ' . $this->_name . ' Benchmark and Informations', true, true);
    }

    public function __destruct() {
        Logger::getInstance()->debug('Engine : ' . $this->_engineName, $this->_name);
        if (Application::getProfiler()) {
            $stats = $this->getStats();
            Logger::getInstance()->debug('Queries : ' . (string) $this->_queryCount . ' (Aproximately memory used  : ' . $stats['ram'] . ' KB in aproximately ' . $stats['time'] . ' ms)', $this->_name);
            Logger::getInstance()->debug('Servers : ' . $this->countServers() . ' (Masters : ' . $this->countServers(Server::TYPE_MASTER) . '  Slaves : ' . $this->countServers(Server::TYPE_SLAVE) . ')', $this->_name);
        }
    }

    public function isValidDriver($driver) {
        if (!$this->_engine)
            throw new \Exception('Please set engine before check driver supported');
        return $this->_engine->isValidDriver($driver);
    }

    // Setters
    public function setName($name) {
        $this->_name = $name;
    }

    public function setEngine($engine) {
        if (!is_string($engine))
            throw new \Exception('Engine parameter must be a string');

        $class = 'framework\database\engines\\' . ucfirst($engine);
        if (!class_exists($class))
            throw new \Exception('Database engine invalid');


        $inst = new \ReflectionClass($class);
        if (!in_array('framework\database\IEngine', $inst->getInterfaceNames()))// check interface
            throw new \Exception('Database engine class must be implement framework\database\IEngine');

        $this->_engine = $inst->newInstance($this->_name);
        $this->_engineName = $class;
    }

    // Getters
    public function getName() {
        return $this->_name;
    }

    public function getEngine() {
        return $this->_engine;
    }

    // Servers
    public function addServers($servers) {
        if (!is_array($servers))
            throw new \Exception('Servers list must be an array');

        foreach ($servers as $server)
            $this->addServer($server);
    }

    public function addServer(Server $server) {
        if ($this->existServer($server))
            throw new \Exception('Already registered server');
        $type = $server->getType();
        if ($type == Server::TYPE_MASTER)
            $this->_masters[] = $server;
        elseif ($type == Server::TYPE_SLAVE)
            $this->_slaves[] = $server;
    }

    public function getServer($type, $dbname = false) {
        $nbServers = $this->countServers($type);
        switch ($type) {
            case Server::TYPE_MASTER:
                if (!$dbname) {
                    if ($nbServers == 0)
                        throw new \Exception('Not servers exists');
                    elseif ($nbServers == 1)
                        return $this->_masters[0];
                    else// Load Balancing
                        return $this->_masters[array_rand($this->_masters)];
                }
                break;
            case Server::TYPE_SLAVE:
                if (!$dbname) {
                    if ($nbServers == 0)
                        return $this->getServer(Server::TYPE_MASTER);
                    elseif ($nbServers == 1)
                        return $this->_slaves[0];
                    else // Load Balancing
                        return $this->_slaves[array_rand($this->_slaves)];
                    break;
                }
            default:
                throw new \Exception('Server type ' . $type . ' don\'t exist !');
                break;
        }
    }

    // Cette fonction retourne tous les serveurs (possible de définir le type), dans array global des serveurs...
    public function getServers($type = null) {
        if ($type == Server::TYPE_MASTER)
            return $this->_masters;
        elseif ($type == Server::TYPE_SLAVE)
            return $this->_slaves;
        else
            return array_merge($this->_masters, $this->_slaves);
    }

    public function countServers($type = null) {
        if ($type == Server::TYPE_MASTER)
            return count($this->_masters);
        elseif ($type == Server::TYPE_SLAVE)
            return count($this->_slaves);
        else
            return count($this->_masters) + count($this->_slaves);
    }

    public function existServer(Server $server) {
        $type = $server->getType();
        switch ($type) {
            case Server::TYPE_MASTER:
                foreach ($this->_masters as &$master)
                    if ($master == $server)
                        return true;
                break;
            case Server::TYPE_SLAVE:
                foreach ($this->_slaves as &$slave)
                    if ($slave == $server)
                        return true;
                break;
            default:
                throw new \Exception('Server type ' . $type . ' don\'t exist !');
        }
        return false;
    }

    public function incrementQueryCount() {
        $this->_queryCount++;
    }

}

?>
