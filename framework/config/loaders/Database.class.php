<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\Database as DatabaseManager;
use framework\database\Server;

class Database extends Loader {

    public function load(Reader $reader) {
        $databases = $reader->read();
        foreach ($databases as $name => $datas) {
            if (!isset($datas['engine']))
                throw new \Exception('Miss engine config param for database : "' . $name . '"');
            if (!isset($datas['server']))
                throw new \Exception('Miss server config param for database : "' . $name . '"');

            $database = new DatabaseManager($name, $datas['engine']);
            foreach ($datas['server'] as $server) {
                if (!isset($server['type']))
                    throw new \Exception('Miss server type');
                if (!isset($server['dbuser']))
                    throw new \Exception('Miss server dbuser type');
                if (!isset($server['dbpassword']))
                    throw new \Exception('Miss server dbpassword type');

                // by dsn
                if (isset($server['dsn'])) {
                    $driver = explode(':', $server['dsn']);
                    if (!$driver || !isset($driver[0]))
                        throw new \Exception('Invalid dsn, please set driver');

                    // delete driver into dsn
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
                    if (!isset($host) || !isset($port) || !isset($dbname) || !isset($charset))
                        throw new \Exception('Invalid dsn, miss parameter');
                } else {
                    //manually
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
                    if (!isset($server['dbuser']))
                        throw new \Exception('Miss server dbuser type');
                    if (!isset($server['dbpassword']))
                        throw new \Exception('Miss server dbpassword type');
                    $dbname = $server['dbname'];
                    if (!isset($server['charset']))
                        throw new \Exception('Miss server charset type');
                    $charset = $server['charset'];
                }
                // Check driver support by database class manager
                if (!$database->isValidDriver($driver))
                    throw new \Exception('Invalid driver : "' . $driver . '", not supported by class manager : "' . (string) $database->class . '"');

                //Create server instance
                $serverInstance = new Server($host, $port, $driver, $dbname, $server['dbuser'], $server['dbpassword'], $server['type'], $charset);
                //add
                $database->addServer($serverInstance);
            }


            // add database
            DatabaseManager::addDatabase($name, $database, true);
        }
    }

}

?>
