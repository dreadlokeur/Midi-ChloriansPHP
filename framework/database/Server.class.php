<?php

namespace framework\database;

class Server {

    const TYPE_MASTER = 'master';
    const TYPE_SLAVE = 'slave';

    protected $_host = null;
    protected $_port = null;
    protected $_driver = '';
    protected $_dbuser = '';
    protected $_dbpassword = '';
    protected $_dbname = null;
    protected $_dbcharset = 'utf8';
    protected $_type = 'master';

    public function __construct($host, $port, $driver, $dbname, $dbuser, $dbpassword, $type, $charset) {
        $this->setHost($host);
        $this->setPort($port);
        $this->setDriver($driver);
        $this->setDbname($dbname);
        $this->setDbuser($dbuser);
        $this->setDbpassword($dbpassword);
        $this->setType($type);
        $this->setDbcharset($charset);
    }

    public function setHost($host) {
        if (!is_string($host))
            throw new \Exception('Host must be a string');
        $this->_host = $host;
        return $this;
    }

    public function setPort($port) {
        $port = (int) $port;
        if ($port < 1 || $port > 65535)
            throw new \Exception('Port must be between 1 and 65535');
        $this->_port = $port;
        return $this;
    }

    public function setDriver($driver) {
        if (!is_string($driver))
            throw new \Exception('driver must be a string');
        $this->_driver = $driver;
        return $this;
    }

    public function setDbuser($user) {
        if (!is_string($user))
            throw new \Exception('Dbuser must be a string');
        $this->_dbuser = $user;
        return $this;
    }

    public function setDbpassword($password) {
        if (!is_string($password))
            throw new \Exception('Dbpassword must be a string');
        $this->_dbpassword = $password;
        return $this;
    }

    public function setDbname($name) {
        if (!is_string($name))
            throw new \Exception('Dbname must be a string');
        $this->_dbname = $name;
        return $this;
    }

    public function setDbcharset($charset) {
        $this->_dbcharset = $charset;
        return $this;
    }

    public function setType($type) {
        if ($type != self::TYPE_MASTER && $type != self::TYPE_SLAVE)
            throw new \Exception('Invalid server type');

        $this->_type = $type;
    }

    public function getHost() {
        return $this->_host;
    }

    public function getPort() {
        return $this->_port;
    }

    public function getDriver() {
        return $this->_driver;
    }

    public function getDbuser() {
        return $this->_dbuser;
    }

    public function getDbpassword() {
        return $this->_dbpassword;
    }

    public function getDbname() {
        return $this->_dbname;
    }

    public function getDbcharset() {
        return $this->_dbcharset;
    }

    public function getType() {
        return $this->_type;
    }

}

?>
