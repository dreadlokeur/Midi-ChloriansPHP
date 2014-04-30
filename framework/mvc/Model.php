<?php

namespace framework\mvc;

use framework\pattern\Factory;
use framework\mvc\model\Entity;

class Model {

    use \framework\pattern\Singleton;

    protected static $_entitiesNamespace = 'models\entities';
    protected static $_reposteriesNamespace = 'models\reposteries';
    protected static $_queryBuilderNamespace = 'framework\mvc\model\queryBuilder';
    protected $_entities = array();
    protected static $_entitiesChecked = array();

    protected function __construct() {
        
    }

    public static function setEntitiesNamespace($namespace) {
        if (!is_string($namespace))
            throw new \Exception('EntitiesNamespace must be a string');
        self::$_entitiesNamespace = $namespace;
    }

    public static function getEntitiesNamespace() {
        return self::$_entitiesNamespace;
    }

    public static function setReposteriesNamespace($namespace) {
        if (!is_string($namespace))
            throw new \Exception('ReposteriesNamespace must be a string');
        self::$_reposteriesNamespace = $namespace;
    }

    public static function getReposteriesNamespace() {
        return self::$_reposteriesNamespace;
    }

    public static function setQueryBuilderNamespace($namespace) {
        if (!is_string($namespace))
            throw new \Exception('QueryBuilderNamespace must be a string');
        self::$_queryBuilderNamespace = $namespace;
    }

    public static function getQueryBuilderNamespace() {
        return self::$_queryBuilderNamespace;
    }

    public static function entityMapChecked($entityName) {
        if (!is_string($entityName))
            throw new \Exception('Entity name must be a string');

        return isset(self::$_entitiesChecked[$entityName]);
    }

    public static function addEntityMapChecked($entityName) {
        if (!is_string($entityName))
            throw new \Exception('Entity name must be a string');

        self::$_entitiesChecked[$entityName] = true;
    }

    public static function factoryEntity($entityName, $entityDatas = array(), $mapColumns = true, $mapRelations = true) {
        $entity = Factory::factory($entityName, $entityDatas, self::getEntitiesNamespace(), null, false, true, 'framework\mvc\model\Entity', true);
        $entity->setName($entityName)->hydrate($entityDatas)->mapping($mapColumns, $mapRelations);
        return $entity;
    }

    public static function factoryRepostery($entityName, $entityDatas = array(), $mapColumns = true, $mapRelations = true) {
        $entity = self::factoryEntity($entityName, $entityDatas = array(), $mapColumns = true, $mapRelations = true);
        return $entity->getRepostery();
    }

    public function getEntityHash(Entity $entity) {
        return spl_object_hash($entity);
    }

    //attach entity into entities list
    public function attach(Entity $entity, $forceReplace = false) {
        $hash = $this->getEntityHash($entity);
        if ($this->isAttached($hash) && !$forceReplace)
            throw new \Exception('Entity "' . $entity->getName() . ' (' . $hash . ')" already attached');

        $this->_entities[$hash] = $entity;
    }

    //detach entity into entities list
    public function detach($entity) {
        if (!is_string($entity) && !is_object($entity))
            throw new \Exception('Entity must be a string or an object');

        if (is_object($entity)) {
            if (!$entity instanceof Entity)
                throw new \Exception('Entity must an instance of framework\mvc\model\Entity');

            $entity = $this->getEntityHash($entity);
        }

        if ($this->isAttached($entity))
            unset($this->_entities[$entity]);
    }

    // check if entity identifier is in entities list
    public function isAttached($entity) {
        if (!is_string($entity) && !is_object($entity))
            throw new \Exception('Entity must be a string or an object');

        if (is_object($entity)) {
            if (!$entity instanceof Entity)
                throw new \Exception('Entity must an instance of framework\mvc\model\Entity');

            $entity = $this->getEntityHash($entity);
        }

        return array_key_exists($entity, $this->_entities);
    }

    // retrieve entity by identifier if is in entities list
    public function getEntity($entity) {
        if ($this->isAttached($entity))
            return $this->_entities[$entity];

        return null;
    }

    // detach all entities
    public function clear() {
        $this->_entities = array();
    }

    public function lock($entity) {// lock an entitie, read or write
    }

    public function unlock($entity) {// unlock entitie
    }

    public function isLocked($entity) {// check if entitie is locked
    }

    /* transactional into bdd */

    //delete into bdd
    public function delete($entity = null) {
        if (!is_null($entity)) {
            if (is_string($entity)) {
                $entity = $this->getEntity($entity);
                if (is_null($entity))
                    return false;
            } elseif (is_object($entity)) {
                if (!$entity instanceof Entity)
                    throw new \Exception('Entity must an instance of framework\mvc\model\Entity');
            } else
                throw new \Exception('Entity must be a string or an object');

            $deleted = $this->_deleteEntity($entity);
            if ($deleted && $this->isAttached($entity))
                $this->detach($entity);
            return $deleted;
        } else { // delete all entities
            $entityDeletedCount = 0;
            foreach ($this->_entities as &$entity) {
                $deleted = $this->_deleteEntity($entity);
                $entityDeletedCount = $entityDeletedCount + $deleted;
                if ($deleted)
                    $this->detach($entity);
            }

            return $entityDeletedCount;
        }
    }

    public function refresh($entity = null) {//cancel object update, and restore bdd info
    }

    public function save($entity = null) {// save into bdd, update if exists (and if is modified) else create into bdd (and set primaryKey value)
    }

    public function flush() {// save and clear all attached entities
    }

    public function getEntities() {
        return $this->_entities;
    }

    public function countEntities() {
        return count($this->_entities);
    }

    protected function _deleteEntity(Entity $entity) {
        $builder = $entity->getRepostery()->getQueryBuilder();
        $table = $entity->getRepostery()->getTable();
        $builder->delete()->from($table->getName(), $table->getAlias());
        //each columns for add where clause
        foreach ($entity->getColumns() as $column) {
            if ($column->isPrimary())
                $builder->addWhere($column->getName(), ':' . $column->getName());
        }
        //prepare query
        $db = $entity->getRepostery()->getDatabaseAdaptater();
        $db->prepare($builder->getQuery());
        //bind parameters
        foreach ($entity->getColumns() as $column) {
            if ($column->isPrimary()) {
                $columnName = $column->getName();
                $columnType = $entity->getRepostery()->getColumnBindType($column->getType());
                $db->bind($entity->$columnName, $columnType, $columnName);
            }
        }
        //exec query and return affected count
        $db->execute();
        return $db->rowCount();
    }

}

?>