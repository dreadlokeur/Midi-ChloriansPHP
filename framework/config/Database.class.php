<?php

namespace framework\config;

use framework\Config;
use framework\Database as Db;
use framework\database\Server;
use framework\utility\Tools;

class Database extends Config {

    protected $_filename = null;
    protected $_format = null;

    public function __construct($filename, $format) {
        if (!file_exists($filename))
            throw new \Exception('File "' . $filename . '" don\'t exists');

        // Check format
        if ($format !== Config::XML && $format !== Config::INI)
            throw new \Exception('Invalid config format');

        $this->_filename = $filename;
        $this->_format = $format;
    }

    public function load() {
        switch ($this->_format) {
            case self::XML:
                $xml = @simplexml_load_file($this->_filename);
                if ($xml === null || $xml === false)
                    throw new \Exception('Invalid xml file');


                // Load databases
                foreach ($xml->database as $database) {
                    // Set and check name
                    if (!isset($database->name))
                        throw new \Exception('Miss database name value');

                    // Drivers, for factory class
                    if (!isset($database->class))
                        throw new \Exception('Miss database class value');

                    //Debug parameter
                    $debug = isset($database->debug) ? Tools::castValue((string) $database->debug) : false;

                    // Create instance of database
                    $databaseInstance = new Db((string) $database->name, (string) $database->class, $debug);

                    // Servers list and parameters
                    if (!isset($database->servers->server))
                        throw new \Exception('Miss server database');
                    else {
                        $servers = array();
                        foreach ($database->servers->server as $server) {
                            if (!isset($server->type))
                                throw new \Exception('Miss server type');

                            if (!isset($server->dbuser))
                                throw new \Exception('Miss server dbuser type');
                            if (!isset($server->dbpassword))
                                throw new \Exception('Miss server dbpassword type');

                            // dbaccess and config by dsn
                            if (isset($server->dsn)) {
                                $driver = explode(':', $server->dsn);
                                if (!$driver || !isset($driver[0]))
                                    throw new \Exception('Invalid dsn, please set driver');
                                
                                // delete driver into dsn
                                $driver = $driver[0];
                                $dsn = str_replace($driver . ':', '', $server->dsn);


                                // Get others infos : host, dbname etc ...
                                $dsnInfos = explode(';', $dsn);
                                foreach ($dsnInfos as &$info) {
                                    $infoData = explode('=', $info);
                                    if (!is_array($infoData))
                                        throw new \Exception('Invalid dsn');

                                    $$infoData[0] = $infoData[1];
                                }
                                if (!isset($host) || !isset($port) || !isset($dbname) || !isset($charset))
                                    throw new \Exception('Invalid dsn, miss parameter');
                            } else {// No dsn, construction manuel ...
                                if (!isset($server->host))
                                    throw new \Exception('Miss server host type');
                                $host = (string) $server->host;
                                if (!isset($server->port))
                                    throw new \Exception('Miss server dbport type');
                                $port = (string) $server->port;
                                if (!isset($server->driver))
                                    throw new \Exception('Miss driver type');
                                $driver = (string) $server->driver;
                                if (!isset($server->dbname))
                                    throw new \Exception('Miss server dbname type');
                                if (!isset($server->dbuser))
                                    throw new \Exception('Miss server dbuser type');
                                if (!isset($server->dbpassword))
                                    throw new \Exception('Miss server dbpassword type');
                                $dbname = (string) $server->dbname;
                                if (!isset($server->charset))
                                    throw new \Exception('Miss server charset type');
                                $charset = (string) $server->charset;
                            }

                            // Check driver support by database class manager
                            if (!$databaseInstance->isValidDriver($driver))
                                throw new \Exception('Invalid driver : "' . $driver . '", not supported by class manager : "' . (string) $database->class . '"');

                            //Create server instance
                            $serverInstance = new Server($host, $port, $driver, $dbname, (string) $server->dbuser, (string) $server->dbpassword, (string) $server->type, $charset);

                            $servers[] = $serverInstance;
                        }
                        // Add servers into Database instance
                        $databaseInstance->addServers($servers);
                        unset($servers, $host, $port, $driver, $dbname, $charset);
                    }

                    // add database
                    Db::addDatabase((string) $database->name, $databaseInstance, true);
                }
                break;
            case self::INI:
                throw new \Exception('NOT YET');
                break;
            default:
                throw new \Exception('Config format must be setted');
                break;
        }
    }

}

?>