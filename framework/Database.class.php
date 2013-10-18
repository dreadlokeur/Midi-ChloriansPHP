<?php

namespace framework;

use framework\database\Server;
use framework\Logger;

class Database {

    use debugger\Debug;

    protected static $_databases = array();
    protected $_name = '';
    protected $_type = null;
    protected $_class = null;
    protected $_masters = array();
    protected $_slaves = array();
    protected $_stats = array('time' => 0, 'ram' => 0); //Queries totals stats

    public static function getDatabase($name) {
        if (!is_string($name))
            throw new \Exception('Database name must be a string');

        if (!array_key_exists($name, self::$_databases)) {
            Logger::getInstance()->debug('Database ' . $name . ' is not setted');
            return false;
        }
        return self::$_databases[$name];
    }

    public static function getDatabases() {
        return self::$_databases;
    }

    public static function addDatabase($name, $conf, $forceReplace = false) {
        if (array_key_exists($name, self::$_databases)) {
            if ($forceReplace)
                throw new \Exception('Database : "' . $name . '" already defined');

            Logger::getInstance()->debug('Database : "' . $name . '" already defined, was overloaded');
        }
        self::$_databases[$name] = $conf;
    }

    public function getStats() {
        return $this->_stats;
    }

    public function setStats($time, $ram) {
        $this->_stats['time'] = $this->_stats['time'] + $time;
        $this->_stats['ram'] = $this->_stats['ram'] + $ram;
    }

    public function __construct($name, $class, $debug = false) {
        $this->setName($name);
        $this->setClass($class);
        self::setDebug($debug);
    }

    public function __destruct() {
        if (self::getDebug()) {
            Logger::getInstance()->addGroup($this->_name, 'Database ' . $this->_name . ' Benchmark and Informations', true);
            $class = $this->getClass();
            $logs = $class->getLogs();
            if (count($logs)) {
                foreach ($logs as &$log) {
                    Logger::getInstance()->debug($log, $this->_name);
                }
            }
            $stats = $this->getStats();
            Logger::getInstance()->debug('Queries : ' . count($logs) . ' (Aproximately memory used  : ' . $stats['ram'] . ' kilo-octets in aproximately ' . $stats['time'] . ' milli-seconds)', $this->_name);
            Logger::getInstance()->debug('Servers : ' . $this->countServers() . ' (Masters : ' . $this->countServers(Server::TYPE_MASTER) . '  Slaves : ' . $this->countServers(Server::TYPE_SLAVE) . ')', $this->_name);
            self::setDebug(false);
        }
    }

    public function isValidDriver($driver) {
        if (!$this->_class)
            throw new \Exception('Please set class befor check driver supported');
        return $this->_class->isValidDriver($driver);
    }

    // Setters
    public function setName($name) {
        $this->_name = $name;
    }

    public function setClass($class) {
        if (!is_string($class))
            throw new \Exception('Class parameter must be a string');

        if (!class_exists('framework\database\drivers\\' . ucfirst($class)))
            throw new \Exception('Database drivers invalid');


        $inst = new \ReflectionClass('\framework\database\drivers\\' . ucfirst($class));
        if (!in_array('framework\database\IDriver', $inst->getInterfaceNames()))// check interface
            throw new \Exception('Database driver class must be implement framework\database\IDriver');

        $this->_class = $inst->newInstance($this->_name);
    }

    // Getters
    public function getName() {
        return $this->_name;
    }

    public function getClass() {
        return $this->_class;
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

        // TODO :
        //   Connection persistente possible sur un serveur
        //   Possiblité de recuperer un serveur précis .... (et pas que celui de la session (persistent)
        //   Améliorer le loadBalancing avec un system de priorité sur le serveur: là c'est un rand aléatoire ok, sauf que si on veut garder certains serveur et 
        //             en gros en "exclure" certains partiellement, pour les serveurs de secours ou autre quoi ...
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

}

?>
