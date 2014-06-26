<?php

namespace framework\mvc\model;

use framework\utility\Tools;
use framework\utility\Validate;

class Annotation {

    protected $_annotation;
    protected $_keys = array();

    public function __construct($annotation, $setKeys = true) {
        if (!is_string($annotation))
            throw new \Exception('Annotation must be a string');

        $this->_annotation = $annotation;

        if ($setKeys)
            $this->setKeys();
    }

    public function getAnnotation() {
        return $this->_annotation;
    }

    public function getKeys() {
        return $this->_keys;
    }

    public function setKeys() {
        //clean
        $keys = explode(',', preg_replace(array('/\*/', '/\s+/', '/\(/', '/\)/'), '', Tools::selectStringByDelimiter($this->getAnnotation(), '(', ')')));
        foreach ($keys as &$key) {
            $keyDatas = explode('=', $key);
            if (!$keyDatas || (!is_array($keyDatas) && count($keyDatas < 2)))
                throw new \Exception('Invalid annotation : "' . $key . '"');
            //check key name
            if (!Validate::isVariableName($keyDatas[0]))
                throw new \Exception('Annotation key : "' . $keyDatas[0] . '" must be a valid variable name');

            $this->_keys[] = array(
                'name' => $keyDatas[0],
                'value' => Tools::castValue(preg_replace(array('/\"/'), '', $keyDatas[1]))
            );
        }
    }

}

?>
