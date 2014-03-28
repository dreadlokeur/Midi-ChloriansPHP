<?php

namespace framework\mvc;

use framework\pattern\Factory;
use framework\utility\Tools;
use framework\utility\Validate;

class Model {

    use \framework\pattern\Singleton;

    protected $_entities = array();

    protected function __construct() {
        
    }

    public static function getAnnotationKeys($annotation) {
        if (!is_string($annotation))
            throw new \Exception('Annotation must be a string');
        //clean
        $keys = explode(',', preg_replace(array('/\*/', '/\s+/', '/\(/', '/\)/'), '', Tools::selectStringByDelimiter($annotation, '(', ')')));
        $annotationKeys = array();
        foreach ($keys as &$key) {
            $keyDatas = explode('=', $key);
            if (!$keyDatas || (!is_array($keyDatas) && count($keyDatas < 2)))
                throw new \Exception('Invalid annotation : "' . $key . '"');
            //check key name
            if (!Validate::isVariableName($keyDatas[0]))
                throw new \Exception('Annotation key : "' . $keyDatas[0] . '" must be a valid variable name');

            $annotationKeys [] = array(
                'name' => $keyDatas[0],
                'value' => Tools::castValue(preg_replace(array('/\"/'), '', $keyDatas[1]))
            );
        }

        return $annotationKeys;
    }

    public static function getProprietyCleanedName(\ReflectionProperty $property) {
        return preg_replace(array('/\_/'), '', $property->getName());
    }

    public function getEntity($entityName, $entityDatas = array(), $mapColumns = true, $mapRelations = true) {
        $entity = Factory::factory($entityName, $entityDatas, 'models', null, false, true, 'framework\mvc\model\Entity', true);
        $entity->setName($entityName)->hydrate($entityDatas)->mapping($mapColumns, $mapRelations);
        return $entity;
    }

    public function getRepostery($reposteryName, $reposteryDatas = array()) {
        if (strripos($reposteryName, 'repostery') === false)
            $reposteryName = $reposteryName . 'Repostery';

        $repostery = Factory::factory($reposteryName, $reposteryDatas, 'models', null, false, true, 'framework\mvc\model\Repostery', true);
        $repostery->setName($reposteryName)->mapping();
        return $repostery;
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

}

?>