<?php

namespace framework\database\engines;

use framework\database\IEngine;
use framework\database\Server;
use framework\utility\Benchmark;
use framework\utility\Validate;
use framework\mvc\Model;
use framework\Database;
use framework\Application;
use framework\Logger;

class Pdo implements IEngine {

    const PARAM_NAMED = 1;
    const PARAM_POSITIONAL = 2;
    const BIND_VALUE = 1;
    const BIND_PARAM = 2;

    //Conn and config
    protected $_configName = null;
    protected $_serverConf = null;
    protected $_connection = null;
    //reqs
    protected $_query = null;
    protected $_statement = null; //PdoStatement
    protected $_params = array();
    protected $_paramsNumberNecesary = 0;
    protected $_namedParamOrder = array();
    protected $_bindParamType = null;
    protected $_paramType = array(
        Model::PARAM_NULL => \PDO::PARAM_NULL,
        Model::PARAM_INT => \PDO::PARAM_INT,
        Model::PARAM_STR => \PDO::PARAM_STR,
        Model::PARAM_LOB => \PDO::PARAM_LOB,
        Model::PARAM_STMT => \PDO::PARAM_STMT,
        Model::PARAM_BOOL => \PDO::PARAM_BOOL,
        Model::PARAM_INPUT_OUTPUT => \PDO::PARAM_INPUT_OUTPUT);
    //For debug message information
    protected $_paramTypeName = array(
        0 => 'null',
        1 => 'int',
        2 => 'str',
        3 => 'lob',
        4 => 'stmt',
        5 => 'bool',
        2147483648 => 'input output'); //

    public function __construct($configName) {
        $this->_configName = $configName;
    }

    public function __destruct() {
        if ($this->_connection)
            $this->disconnect();
    }

    public function isValidDriver($driver) {
        if (!is_string($driver))
            return false;
        return in_array($driver, \PDO::getAvailableDrivers());
    }

    public function connection($serverType) {
        $server = Database::getDatabase($this->_configName)->getServer($serverType);
        if ($server !== $this->_serverConf) {
            if ($this->_connection)
                $this->disconnect();

            $this->_serverConf = $server;
            // Connect
            try {
                $dsn = $this->_serverConf->getDriver() . ':dbname=' . $this->_serverConf->getDbname() . ';host=' . $this->_serverConf->getHost() . ';port=' . $this->_serverConf->getPort() . ';charset=' . $this->_serverConf->getDbcharset();
                $this->_connection = new \PDO($dsn, $this->_serverConf->getDbuser(), $this->_serverConf->getDbpassword());
            } catch (\PDOException $e) {
                throw new \Exception('Error : ' . $e->getMessage() . ' N° : ' . $e->getCode() . '');
            }
        }

        Logger::getInstance()->debug('Connect server : "' . $dsn . '"', $this->_configName);
        return $this;
    }

    public function disconnect() {
        if ($this->haveStatement())
            $this->_closeStatement();

        // Close connexion
        if ($this->_connection)
            $this->_connection = null;

        // Clean server Configuration
        if ($this->_serverConf)
            $this->_serverConf = null;

        return $this;
    }

    public function haveStatement() {
        return ($this->_statement !== null && $this->_statement !== false);
    }

    public function set($query, $options = array()) {
        if (!is_string($query))
            throw new \Exception('Query must be a string');

        //Clean
        $this->_closeStatement();

        $this->_query = $query;
        $server = $this->isReadQuery($this->_query) ? Server::TYPE_SLAVE : Server::TYPE_MASTER;
        $this->connection($server);

        // Check query and determine paramters type (by position with ? or by name with :name)
        preg_match_all('#:([0-9a-zA-Z_-]+)#', $query, $namedParam);
        if (count($namedParam[1]) > 0) {
            if (strpos($this->_query, '?') !== false)
                throw new \Exception('You cannot mixed positional and named parameter on query');
            $query = preg_replace('#:([0-9a-zA-Z_-]+)#', '?', $query);
            // set param bind type to named
            $this->_bindParamType = self::PARAM_NAMED;
            $this->_namedParamOrder = $namedParam[1];
        }
        else
            $this->_bindParamType = self::PARAM_POSITIONAL;


        // Count parameters necessary
        $this->_paramsNumberNecesary = $this->_bindParamType === self::PARAM_POSITIONAL ? substr_count($this->_query, '?') : count($namedParam[1]);

        // Now prepare : create PdoStatement
        $this->_statement = $this->_connection->prepare($this->_query, $options);

        if (!$this->_statement)
            throw new \Exception('Error when prepare your query : ' . $this->error);

        return $this;
    }

    public function bind($value, $type, $key = false, $bindType = self::BIND_PARAM) {
        if ($bindType != self::BIND_VALUE && $bindType != self::BIND_PARAM)
            throw new \Exception('Invalid bind type');

        if (!is_string($type) && !is_int($type))
            throw new \Exception('Type "' . $type . '" must be an integer or a string');
        if (!array_key_exists($type, $this->_paramType))
            throw new \Exception('Type "' . $type . '" don\'t exist');

        // If key setted, check if it's variable normalization format
        if ($key !== false && !Validate::isVariableName($key))
            throw new \Exception('Key for param must bet start with letter and can have caracters : a-zA-Z0-9_-');

        // Search if is not mixed key format
        if ($key !== false && $this->_bindParamType === self::PARAM_POSITIONAL)
            throw new \Exception('You cannot mixed positionnal and named parameter');
        if ($key === false && count($this->_params) > 0 && $this->_bindParamType === self::PARAM_NAMED)
            throw new \Exception('You cannot mixed positionnal and named parameter');

        // Add datas on params array
        if ($key) {
            $this->_params[$key] = array(
                'value' => $value,
                'type' => $this->_paramType[$type],
                'bindType' => $bindType
            );
        } else {
            $this->_params[] = array(
                'value' => $value,
                'type' => $this->_paramType[$type],
                'bindType' => $bindType
            );
        }
        return $this;
    }

    public function execute(
    $closeStatement = false) {
        if (Application::getDebug())
            Benchmark::getInstance($this->_configName)->startTime()->startRam();

        if ($this->_query === null || !$this->haveStatement())
            throw new \Exception('Set query before execute...');

        if ($this->_paramsNumberNecesary != count($this->_params))
            throw new \Exception('Miss bind parameters');

        // Bind parameters
        $i = 0;
        foreach ($this->_params as $param) {
            $bindName = $this->_bindParamType === self::PARAM_POSITIONAL ? $i + 1 : ':' . $this->_namedParamOrder[$i];
            if ($param['bindType'] == self::BIND_PARAM)
                $this->_statement->bindParam($bindName, $param['value'], $param['type']);
            else
                $this->_statement->bindValue($bindName, $param['value'], $param['type']);

            $i++;
        }
        // Execute
        $this->_statement->execute();

        // Debug
        if (Application::getDebug()) {
            $error = $this->_statement->errorInfo();
            $errorMessage = $error && isset($error[2]) ? $error[2] : 'void';

            $parameters = '';
            foreach ($this->_params as $param)
                $parameters .=
                        (string) $param['value'] . ' (Type ' . $this->_paramTypeName[$param['type']] . ') ';

            $time = Benchmark::getInstance($this->_configName)->stopTime()->getStatsTime();
            $ram = Benchmark::getInstance($this->_configName)->stopRam()->getStatsRam();
            Logger::getInstance()->debug('Query : ' . $this->_query . ' with parameters values : "' . trim($parameters, ' ') . '" Time : ' . $time . ' ms Ram : ' . $ram . ' KB Error : ' . $errorMessage, $this->_configName);
            Database::getDatabase($this->_configName)->setStats($time, $ram);
            Database::getDatabase($this->_configName)->incrementQueryCount();
        }

        // Close
        if ($closeStatement)
            $this->_closeStatement();

        return $this;
    }

    public function fetch($fetchStyle = \PDO::FETCH_BOTH, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $offset = false) {
        if (!$this->haveStatement())
            throw new \Exception('You must execute query before fetch result');

        $this->_statement->fetch($fetchStyle, $cursorOrientation, $offset);
    }

    public function fetchAll($fetchStyle = \PDO::FETCH_BOTH, $fetchArgument = false, $ctorArgs = false) {
        if (!$this->haveStatement())
            throw new \Exception('You must execute query before fetch result');

        if ($fetchArgument == \PDO::FETCH_CLASS) {
            if ($ctorArgs)
                return $this->_statement->fetchAll($fetchStyle, $fetchArgument, $ctorArgs);
            else
                return $this->_statement->fetchAll($fetchStyle, $fetchArgument);
        }
        else
            return $this->_statement->fetchAll($fetchStyle);
    }

    public function isReadQuery($query) {
        return stripos($query, 'select') !== false || stripos($query, 'show') !== false || stripos($query, 'describe') !== false;
    }

    protected function _closeStatement() {
        if ($this->haveStatement())
            $this->_statement->closeCursor();

        $this->_query = null;
        $this->_params = array();
        $this->_paramsNumberNecesary = 0;
        $this->_bindParamType = null;
        $this->_namedParamOrder = array();
    }

}

?>