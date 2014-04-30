<?php

/*
 * find, findBy, findByX, findAll, findOne, findOneBy, findOneByX, count, exist
 * load, loadLazy..., 
 * queryBuilder,
 * debug mode, benchmark, profiler
 * 
 */

namespace framework\mvc\model;

use framework\Database;
use framework\mvc\Model;
use framework\mvc\model\Table;
use framework\pattern\Factory;
use framework\mvc\model\QueryBuilder;

class Repostery {

    protected $_name;
    protected $_isMapped = false;
    protected $_table;
    protected $_queryBuilder;
    protected $_databaseConfigName;
    protected $_database = null;
    protected $_databaseAdaptater;
    protected static $_databaseConfigNameDefault = 'default';

    public static function setDatabaseConfigNameDefault($configName) {
        self::$_databaseConfigNameDefault = $configName;
    }

    public static function getDatabaseConfigNameDefault() {
        return self::$_databaseConfigNameDefault;
    }

    public function setName($name) {
        $name = explode('\\', (string) $name);
        if (is_array($name))
            $name = end($name);

        $this->_name = strtolower((string) $name);
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function mapping($forceMap = false) {
        if ($this->isMapped() && !$forceMap)
            throw new \Exception('Repostery : "' . $this->getName() . '" already mapped');

        $reflexionClass = new \ReflectionClass($this);
        //map default repostery datas (table, tableAlias, databaseConfigName, queryBuilder)
        $doc = $reflexionClass->getDocComment();
        $tableName = false;
        $tableAlias = null;
        $queryBuilderName = false;
        if (preg_match('/@repostery/', $doc)) {
            $annotation = new Annotation($doc);
            $annotationKeys = $annotation->getKeys();
            foreach ($annotationKeys as $annotationKey) {
                switch ($annotationKey['name']) {
                    case 'table':
                        $tableName = $annotationKey['value'];
                        break;
                    case 'tableAlias':
                        $tableAlias = $annotationKey['value'];
                        break;
                    case 'databaseConfigName':
                        $this->setDatabase($annotationKey['value']);
                        break;
                    case 'queryBuilder':
                        $queryBuilderName = $annotationKey['value'];
                        break;
                    default:
                        break;
                }
            }
        }
        // no table defined in repostery annotation, set manualy, by repostery name
        if (!$tableName)
            $tableName = $this->getName();

        // set table instance
        $table = new Table($tableName, $tableAlias);
        $this->setTable($table);

        //set default database
        if (is_null($this->getDatabase()))
            $this->setDatabase(self::getDatabaseConfigNameDefault());


        //set query builder
        $queryBuilderNs = $queryBuilderName ? null : Model::getQueryBuilderNamespace();
        if (!$queryBuilderName)
            $queryBuilderName = $this->getDatabase()->getType();

        $builder = Factory::factory($queryBuilderName, array(), $queryBuilderNs, 'framework\mvc\model\IQueryBuilder');
        $this->setQueryBuilder(new QueryBuilder($builder));


        $this->_isMapped = true;
    }

    public function setTable(Table $table) {
        $this->_table = $table;
    }

    public function getTable() {
        return $this->_table;
    }

    public function setDatabase($configDatabaseName) {
        $database = Database::getDatabase($configDatabaseName);
        if (!$database)
            throw new \Exception('Invalid database config name');

        $this->_databaseConfigName = $configDatabaseName;
        $this->_database = $database;
        $this->_databaseAdaptater = $this->_database->getAdaptater();

        return $this;
    }

    public function getDatabaseAdaptater() {
        return $this->_databaseAdaptater;
    }

    public function getDatabase() {
        return $this->_database;
    }

    public function getDatabaseConfigName() {
        return $this->_databaseConfigName;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder) {
        $this->_queryBuilder = $queryBuilder->getBuilder();
    }

    public function getQueryBuilder() {
        return $this->_queryBuilder;
    }

    public function isMapped() {
        return $this->_isMapped;
    }

    public function getColumnBindType($type) {
        switch ($type) {
            case Column::TYPE_INTEGER:
            case Column::TYPE_SMALLINT:
            case Column::TYPE_BIGINT:
            case Column::TYPE_DECIMAL:
            case Column::TYPE_FLOAT:
                return Database::PARAM_INT;
            case Column::TYPE_BOOLEAN:
                return Database::PARAM_BOOL;
            default:
                return Database::PARAM_STR;
        }
    }

    // transactional into bdd
    public function find() {
        
    }

    public function findAll() {
        
    }

}

?>
