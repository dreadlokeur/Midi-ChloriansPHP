<?php

namespace framework\mvc\model;

use framework\mvc\model\Entity;

class EntityManager {

    use \framework\pattern\Singleton;

    protected function __construct() {
        
    }

    // enties...
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

    // bdd
    public function delete($entity = null) {//delete into bdd
    }

    public function refresh($entity = null) {//cancel object update, and restore bdd info
    }

    public function save($entity = null) {// save into bdd, update if exists, create else
    }

}

?>