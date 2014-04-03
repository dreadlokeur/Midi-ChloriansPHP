<?php

namespace framework\mvc;

use framework\pattern\Factory;
use framework\mvc\model\Entity;

class Model {

    use \framework\pattern\Singleton;

    protected $_entities = array();

    protected function __construct() {
        
    }

    public static function factoryEntity($entityName, $entityDatas = array(), $mapColumns = true, $mapRelations = true) {
        $entity = Factory::factory($entityName, $entityDatas, 'models', null, false, true, 'framework\mvc\model\Entity', true);
        $entity->setName($entityName)->hydrate($entityDatas)->mapping($mapColumns, $mapRelations);
        return $entity;
    }

    public static function factoryRepostery($entityName, $entityDatas = array(), $mapColumns = true, $mapRelations = true) {
        $entity = self::factoryEntity($entityName, $entityDatas = array(), $mapColumns = true, $mapRelations = true);
        return $entity->getRepostery();
    }

    // enties manager...
    public function attach(Entity $entity) {//attach entity into entities list
    }

    public function detach(Entity $entity) {//detach entity into entities list
    }

    public function isAttached($entity) {// check if entity identifier is in entities list
    }

    public function find($entity) {// retrieve entity by identifier if is in entities list
    }

    public function clear($entity = null) {// detach all entities
    }

    public function lock($entity) {// lock an entitie, read or write
    }

    public function unlock($entity) {// unlock entitie
    }

    public function isLocked($entity) {// check if entitie is locked
    }

    // transactional into bdd
    public function delete($entity = null) {//delete into bdd
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

}

?>