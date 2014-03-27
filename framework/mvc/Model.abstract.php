<?php

namespace framework\mvc;

use framework\pattern\Factory;

abstract class Model {

    final public static function factoryEntity($entityName, $entityDatas = array(), $loadColumns = true, $loadRelations = true) {
        $entity = Factory::factory($entityName, $entityDatas, 'models', null, false, true, 'framework\mvc\model\Entity', true);
        $entity->setName($entityName)->hydrate($entityDatas)->mapping($loadColumns, $loadRelations);
        return $entity;
    }

    final public static function factoryRepostery() {
        
    }

    final public static function getEntityManager() {
        
    }

}

?>