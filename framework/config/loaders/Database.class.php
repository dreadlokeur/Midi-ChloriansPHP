<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\Database as DatabaseManager;
use framework\database\Server;
use framework\utility\Validate;

class Database extends Loader {

    public function load(Reader $reader) {
        $databases = $reader->read();
        foreach ($databases as $name => $datas) {
            // Check name
            if (!Validate::isVariableName($name))
                throw new \Exception('Name of database must be a valid variable name');

            // Check essential parameters
            if (!isset($datas['engine']))
                throw new \Exception('Miss engine config param for database : "' . $name . '"');
            if (!isset($datas['server']))
                throw new \Exception('Miss server config param for database : "' . $name . '"');

            // Create database instance
            $database = new DatabaseManager($name, $datas['engine']);

            // Fetch servers
            foreach ($datas['server'] as $server) {
                // Check essential parameters (type, user, password)
                if (!isset($server['type']))
                    throw new \Exception('Miss server type');
                if (!isset($server['dbuser']))
                    throw new \Exception('Miss server dbuser type');
                if (!isset($server['dbpassword']))
                    throw new \Exception('Miss server dbpassword type');

                //Check database connection info
                if (isset($server['dsn'])) {// by dsn
                    $driver = explode(':', $server['dsn']);
                    if (!$driver || !isset($driver[0]))
                        throw new \Exception('Invalid dsn, please set driver');

                    // Delete driver into dsn
                    $driver = $driver[0];
                    $dsn = str_replace($driver . ':', '', $server['dsn']);


                    // Get others infos : host, dbname etc ...
                    $dsnInfos = explode(';', $dsn);
                    foreach ($dsnInfos as &$info) {
                        $infoData = explode('=', $info);
                        if (!is_array($infoData))
                            throw new \Exception('Invalid dsn');

                        $$infoData[0] = $infoData[1];
                    }
                    // Required infos
                    if (!isset($host) || !isset($port) || !isset($dbname) || !isset($charset))
                        throw new \Exception('Invalid dsn, miss parameter');
                } else {//manually
                    if (!isset($server['host']))
                        throw new \Exception('Miss server host type');
                    $host = $server['host'];
                    if (!isset($server['port']))
                        throw new \Exception('Miss server dbport type');
                    $port = $server['port'];
                    if (!isset($server['driver']))
                        throw new \Exception('Miss driver type');
                    $driver = $server['driver'];
                    if (!isset($server['dbname']))
                        throw new \Exception('Miss server dbname type');
                    $dbname = $server['dbname'];
                    if (!isset($server['charset']))
                        throw new \Exception('Miss server charset type');
                    $charset = $server['charset'];
                }


                // Check driver is supported by database engine
                if (!$database->isValidDriver($driver))
                    throw new \Exception('Invalid driver : "' . $driver . '", not supported database engine : "' . $datas['engine'] . '"');

                // Create server instance
                $serverInstance = new Server($host, $port, $driver, $dbname, $server['dbuser'], $server['dbpassword'], $server['type'], $charset);
                // Add into servers list
                $database->addServer($serverInstance);
            }


            // Add database
            DatabaseManager::addDatabase($name, $database, true);
        }
    }

}

?>
